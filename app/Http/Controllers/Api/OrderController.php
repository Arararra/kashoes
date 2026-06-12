<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;

class OrderController extends ResourceController
{
    protected function modelClass(): string
    {
        return Order::class;
    }

    protected function validationRules(string $action): array
    {
        $user = request()->user();
        $isCustomer = $user && $user->hasRole('customer');

        $rules = [
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'customer_address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'services' => 'required|array',
            'services.*.service_id' => 'required|integer', 
            'services.*.price' => 'required|numeric', 
            'services.*.quantity' => 'required|integer', 
            'services.*.description' => 'nullable|string',
            'total_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'status' => 'required|string',
            'estimated_finished_date' => 'required|date',
            'finished_date' => 'nullable|date',
            'created_by' => 'nullable|integer|exists:users,id',
        ];

        if (!$isCustomer) {
            $rules['customer_id'] = 'required|integer|exists:customers,id';
        }

        if ($action !== 'store') {
            // Make all rules sometimes for update
            $rules = array_map(function($rule) {
                return 'sometimes|' . $rule;
            }, $rules);
        }

        return $rules;
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        $user = request()->user();
        if ($user && $user->hasRole('customer')) {
            $customer = \App\Models\Customer::where('user_id', $user->id)->first();
            if (!$customer) {
                return response()->json([]);
            }
            return response()->json(Order::where('customer_id', $customer->id)->latest()->get());
        }

        return response()->json(Order::latest()->get());
    }

    public function store(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $data = $request->validate($this->validationRules('store'));

        if ($user && $user->hasRole('customer')) {
            $customer = \App\Models\Customer::where('user_id', $user->id)->first();
            if ($customer) {
                $data['customer_id'] = $customer->id;
                $data['customer_name'] = $customer->name;
                
                // Update customer phone/address if it was empty and provided in order
                if (empty($customer->phone) && !empty($data['customer_phone'])) {
                    $customer->update(['phone' => $data['customer_phone']]);
                }
                if (empty($customer->address) && !empty($data['customer_address'])) {
                    $customer->update(['address' => $data['customer_address']]);
                }
            }
        }

        $model = Order::create($data);
        return response()->json($model, 201);
    }
}
