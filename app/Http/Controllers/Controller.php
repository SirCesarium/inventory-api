<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

abstract class Controller
{
    protected function perPage(): int
    {
        return max(1, min(100, (int) request()->query('per_page', 15)));
    }

    protected function performForceDestroy(Model $model, string $gate): JsonResponse
    {
        Gate::authorize($gate);
        $model->forceDelete();

        return response()->json(['message' => class_basename($model).' permanently deleted.'], 200);
    }
}
