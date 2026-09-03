<?php

namespace App\Http\Controllers\Api;

use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
            ]
            : [
                'name' => 'sometimes|string',
                'price' => 'sometimes|numeric|min:0',
            ];
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Service::class);
        $data = $request->validate($this->validationRules('store'));
        $data['created_by'] = $request->user()->id;

        return response()->json(Service::create($data), 201);
    }
}
