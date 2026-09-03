<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends ResourceController
{
    protected function modelClass(): string
    {
        return User::class;
    }

    protected function validationRules(string $action): array
    {
        $id = request()->route('id');

        return $action === 'store'
            ? [
                'name' => 'required|string',
                'phone' => 'required|string',
                'address' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
            ]
            : [
                'name' => 'sometimes|string',
                'phone' => 'sometimes|string',
                'address' => 'sometimes|string',
                'email' => 'sometimes|email|unique:users,email,'.$id,
                'password' => 'sometimes|string|min:8',
            ];
    }

    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        return response()->json(User::with(['customer', 'roles'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', User::class);
        $data = $request->validate($this->validationRules('store'));
        $data['created_by'] = $request->user()->id;

        return response()->json(User::create($data), 201);
    }
}
