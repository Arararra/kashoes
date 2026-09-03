<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class OrderController extends ResourceController
{
    private const STATUSES = ['pending', 'in_progress', 'ready_for_pickup', 'completed', 'cancelled'];

    protected function modelClass(): string
    {
        return Order::class;
    }

    protected function validationRules(string $action): array
    {
        $required = $action === 'store' ? 'required' : 'sometimes';

        return [
            'customer_id' => [$required, 'integer', 'exists:customers,id'],
            'customer_name' => [$required, 'string', 'max:255'],
            'customer_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'customer_address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'services' => [$required, 'array', 'min:1'],
            'services.*.service_id' => ['required', 'integer', 'distinct', 'exists:services,id'],
            'services.*.price' => ['sometimes', 'numeric'],
            'services.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'services.*.description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'total_price' => ['sometimes', 'numeric'],
            'discount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status' => [$required, 'in:'.implode(',', self::STATUSES)],
            'estimated_finished_date' => [$required, 'date'],
            'finished_date' => ['prohibited'],
            'created_by' => ['sometimes', 'integer'],
        ];
    }

    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Order::class);
        $user = request()->user();

        if (! $this->isAdmin($user)) {
            $customer = Customer::where('user_id', $user->id)->first();

            return response()->json(
                $customer ? Order::where('customer_id', $customer->id)->latest()->get() : [],
            );
        }

        return response()->json(Order::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Order::class);
        $user = $request->user();
        $isAdmin = $this->isAdmin($user);
        $rules = $this->validationRules('store');

        if (! $isAdmin) {
            $rules['customer_id'] = ['sometimes', 'integer'];
            $rules['customer_name'] = ['sometimes', 'string', 'max:255'];
            $rules['status'] = ['sometimes', 'in:pending'];
        }

        $data = $request->validate($rules);

        $order = DB::transaction(function () use ($data, $user, $isAdmin): Order {
            $customer = $isAdmin
                ? Customer::findOrFail($data['customer_id'])
                : Customer::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name' => $user->name,
                        'phone' => $data['customer_phone'] ?? $user->phone ?? '',
                        'address' => $data['customer_address'] ?? $user->address ?? '',
                        'is_member' => false,
                    ],
                );

            if (! $isAdmin) {
                $changes = [];
                if (blank($customer->phone) && filled($data['customer_phone'] ?? null)) {
                    $changes['phone'] = $data['customer_phone'];
                }
                if (blank($customer->address) && filled($data['customer_address'] ?? null)) {
                    $changes['address'] = $data['customer_address'];
                }
                if ($changes !== []) {
                    $customer->update($changes);
                }
            }

            [$services, $subtotal] = $this->canonicalServices($data['services']);
            $discount = $isAdmin ? (float) ($data['discount'] ?? 0) : 0.0;
            $this->ensureDiscountIsValid($discount, $subtotal);

            return Order::create([
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_address' => $customer->address,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'services' => $services,
                'total_price' => $subtotal - $discount,
                'discount' => $discount,
                'status' => $isAdmin ? ($data['status'] ?? 'pending') : 'pending',
                'estimated_finished_date' => $data['estimated_finished_date'],
                'created_by' => $user->id,
            ]);
        });

        return response()->json($order, 201);
    }

    public function show($id): JsonResponse
    {
        $order = Order::findOrFail($id);
        Gate::authorize('view', $order);

        return response()->json($order);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        Gate::authorize('update', $order);

        if (! $this->isAdmin($request->user())) {
            $data = $request->validate([
                'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
                'customer_id' => ['prohibited'],
                'services' => ['prohibited'],
                'total_price' => ['prohibited'],
                'discount' => ['prohibited'],
                'status' => ['prohibited'],
                'finished_date' => ['prohibited'],
                'created_by' => ['prohibited'],
            ]);
            $order->update($data);

            return response()->json($order->fresh());
        }

        $data = $request->validate($this->validationRules('update'));

        DB::transaction(function () use ($order, $data): void {
            unset($data['total_price'], $data['created_by'], $data['finished_date']);

            if (array_key_exists('customer_id', $data)) {
                $customer = Customer::findOrFail($data['customer_id']);
                $data['customer_name'] = $customer->name;
                $data['customer_phone'] = $customer->phone;
                $data['customer_address'] = $customer->address;
            }

            if (array_key_exists('services', $data)) {
                [$data['services'], $subtotal] = $this->canonicalServices($data['services']);
                $discount = (float) ($data['discount'] ?? $order->discount ?? 0);
                $this->ensureDiscountIsValid($discount, $subtotal);
                $data['total_price'] = $subtotal - $discount;
            } elseif (array_key_exists('discount', $data)) {
                $subtotal = (float) $order->total_price + (float) $order->discount;
                $discount = (float) ($data['discount'] ?? 0);
                $this->ensureDiscountIsValid($discount, $subtotal);
                $data['total_price'] = $subtotal - $discount;
            }

            $order->update($data);
        });

        return response()->json($order->fresh());
    }

    public function destroy($id): JsonResponse
    {
        $order = Order::findOrFail($id);
        Gate::authorize('delete', $order);
        $order->delete();

        return response()->json(null, 204);
    }

    private function canonicalServices(array $items): array
    {
        $catalog = Service::whereIn('id', collect($items)->pluck('service_id'))->get()->keyBy('id');
        $subtotal = 0.0;

        $services = collect($items)->map(function (array $item) use ($catalog, &$subtotal): array {
            $service = $catalog->get((int) $item['service_id']);
            $quantity = (int) $item['quantity'];
            $price = (float) $service->price;
            $subtotal += $price * $quantity;

            return [
                'service_id' => $service->id,
                'price' => $price,
                'quantity' => $quantity,
                'description' => filled($item['description'] ?? null)
                    ? trim((string) $item['description'])
                    : $service->name,
            ];
        })->all();

        return [$services, $subtotal];
    }

    private function ensureDiscountIsValid(float $discount, float $subtotal): void
    {
        if ($discount > $subtotal) {
            throw ValidationException::withMessages([
                'discount' => 'Diskon tidak boleh melebihi subtotal pesanan.',
            ]);
        }
    }

    private function isAdmin($user): bool
    {
        return $user?->hasAnyRole(['admin', 'super_admin']) ?? false;
    }
}
