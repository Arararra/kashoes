<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class ResourceController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function validationRules(string $action): array;

    protected function newModel(): Model
    {
        return new ($this->modelClass());
    }

    public function index(): JsonResponse
    {
        return response()->json($this->newModel()->all());
    }

    public function store(Request $request): JsonResponse
    {
        $model = $this->newModel()->create($request->validate($this->validationRules('store')));

        return response()->json($model, 201);
    }

    public function show($id): JsonResponse
    {
        return response()->json($this->newModel()->findOrFail($id));
    }

    public function update(Request $request, $id): JsonResponse
    {
        $model = $this->newModel()->findOrFail($id);
        $model->update($request->validate($this->validationRules('update')));

        return response()->json($model);
    }

    public function destroy($id): JsonResponse
    {
        $this->newModel()->findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
