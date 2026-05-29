<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class PermissionController extends Controller
{
    /**
     * List permissions
     */
    public function index(): JsonResponse
    {
        Gate::authorize('permissions.read');

        $permissions = Permission::with('roles')->latest()->paginate($this->perPage());

        return response()->json($permissions, 200);
    }

    /**
     * Get permission
     */
    public function show(Permission $permission): JsonResponse
    {
        Gate::authorize('permissions.read');

        $permission->load('roles');

        return response()->json($permission, 200);
    }

    /**
     * List all available permissions from config
     */
    public function available(): JsonResponse
    {
        Gate::authorize('permissions.read');

        return response()->json(config('permissions'), 200);
    }
}
