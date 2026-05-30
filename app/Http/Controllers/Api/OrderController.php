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
        return $action === 'store'
            ? [
                'customer_id' => 'nullable|integer|exists:customers,id',
                'customer_name' => 'required|string',
                'customer_phone' => 'required|string',
                'customer_address' => 'required|string',
                'services' => 'required|array',
                'services.*' => 'string',
                'total_price' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'status' => 'required|string',
                'estimated_finished_date' => 'nullable|date',
                'finished_date' => 'nullable|date',
                'created_by' => 'nullable|integer|exists:users,id',
            ]
            : [
                'customer_id' => 'sometimes|nullable|integer|exists:customers,id',
                'customer_name' => 'sometimes|string',
                'customer_phone' => 'sometimes|string',
                'customer_address' => 'sometimes|string',
                'services' => 'sometimes|array',
                'services.*' => 'string',
                'total_price' => 'sometimes|numeric|min:0',
                'discount' => 'sometimes|numeric|min:0',
                'status' => 'sometimes|string',
                'estimated_finished_date' => 'sometimes|nullable|date',
                'finished_date' => 'sometimes|nullable|date',
                'created_by' => 'nullable|integer|exists:users,id',
            ];
    }
}
