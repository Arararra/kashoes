<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CustomerController extends ResourceController
{
    protected function modelClass(): string
    {
        return Customer::class;
    }

    protected function validationRules(string $action): array
    {
        return $action === 'store'
            ? [
                'name' => 'required|string',
                'phone' => 'nullable|string',
                'address' => 'nullable|string',
                'is_member' => 'sometimes|boolean',
            ]
            : [
                'name' => 'sometimes|string',
                'phone' => 'sometimes|string',
                'address' => 'sometimes|string',
                'is_member' => 'sometimes|boolean',
            ];
    }

    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Customer::class);
        $user = request()->user();
        if ($user && $user->hasRole('customer')) {
            $customer = \App\Models\Customer::where('user_id', $user->id)->first();
            if (! $customer) {
                return response()->json([]);
            }

            return response()->json([$customer]);
        }

        return response()->json(Customer::latest()->get());
    }

    public function update(Request $request, $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        Gate::authorize('update', $customer);

        $rules = $this->validationRules('update');
        if (! $request->user()->hasAnyRole(['admin', 'super_admin'])) {
            unset($rules['is_member']);
        }

        $customer->update($request->validate($rules));

        return response()->json($customer);
    }
}
