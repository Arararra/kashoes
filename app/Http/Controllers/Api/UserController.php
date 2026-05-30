<?php

namespace App\Http\Controllers\Api;

use App\Models\User;

class UserController extends ResourceController
{
    protected function modelClass(): string
    {
        return User::class;
    }

    protected function validationRules(string $action): array
    {
        return $action === 'store'
            ? [
                'name' => 'required|string',
                'phone' => 'required|string',
                'address' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'created_by' => 'nullable|integer|exists:users,id',
            ]
            : [
                'name' => 'sometimes|string',
                'phone' => 'sometimes|string',
                'address' => 'sometimes|string',
                'email' => 'sometimes|email|unique:users,email',
                'password' => 'sometimes|string|min:8',
                'created_by' => 'nullable|integer|exists:users,id',
            ];
    }
}
