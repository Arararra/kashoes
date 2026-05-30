<?php

namespace App\Http\Controllers\Api;

use App\Models\Service;

class ServiceController extends ResourceController
{
    protected function modelClass(): string
    {
        return Service::class;
    }

    protected function validationRules(string $action): array
    {
        return $action === 'store'
            ? [
                'name' => 'required|string',
                'price' => 'required|numeric|min:0',
                'created_by' => 'nullable|integer|exists:users,id',
            ]
            : [
                'name' => 'sometimes|string',
                'price' => 'sometimes|numeric|min:0',
                'created_by' => 'nullable|integer|exists:users,id',
            ];
    }
}
