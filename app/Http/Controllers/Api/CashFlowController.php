<?php

namespace App\Http\Controllers\Api;

use App\Models\CashFlow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
                'type' => 'required|in:income,expense',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'amount' => 'required|numeric|gt:0',
            ]
            : [
                'date' => 'sometimes|date',
                'type' => 'sometimes|in:income,expense',
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'amount' => 'sometimes|numeric|gt:0',
            ];
    }

    public function index(): JsonResponse
    {
        $this->ensureAdmin(request());

        return response()->json(
            CashFlow::query()->orderByDesc('date')->orderByDesc('id')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);
        $data = $request->validate($this->validationRules('store'));
        $data['created_by'] = $request->user()->id;

        return response()->json(CashFlow::create($data), 201);
    }

    public function show($id): JsonResponse
    {
        $this->ensureAdmin(request());

        return parent::show($id);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $this->ensureAdmin($request);

        return parent::update($request, $id);
    }

    public function destroy($id): JsonResponse
    {
        $this->ensureAdmin(request());

        return parent::destroy($id);
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless(
            $request->user()?->hasAnyRole(['admin', 'super_admin']),
            403,
            'Hanya admin yang dapat mengelola arus kas.'
        );
    }
}
