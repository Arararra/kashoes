<?php

namespace App\Http\Controllers\Api;

use App\Models\CashFlow;

class CashFlowController extends ResourceController
{
    protected function modelClass(): string
    {
        return CashFlow::class;
    }

    protected function validationRules(string $action): array
    {
        return $action === 'store'
            ? [
                'date' => 'required|date',
                'type' => 'required|string',
                'title' => 'required|string',
                'description' => 'nullable|string',
                'amount' => 'required|numeric|min:0',
                'created_by' => 'nullable|integer|exists:users,id',
            ]
            : [
                'date' => 'sometimes|date',
                'type' => 'sometimes|string',
                'title' => 'sometimes|string',
                'description' => 'nullable|string',
                'amount' => 'sometimes|numeric|min:0',
                'created_by' => 'nullable|integer|exists:users,id',
            ];
    }
}
