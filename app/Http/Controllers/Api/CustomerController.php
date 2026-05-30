<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;

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
                'phone' => 'required|string',
                'address' => 'required|string',
                'is_member' => 'required|boolean',
            ]
            : [
                'name' => 'sometimes|string',
                'phone' => 'sometimes|string',
                'address' => 'sometimes|string',
                'is_member' => 'sometimes|boolean',
            ];
    }
}
