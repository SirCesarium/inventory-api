<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PermissionController extends Controller
{
    /**
     * List permissions
     */
    public function index(): JsonResponse
    {
        Gate::authorize('manage-permissions');

        $permissions = Permission::with('roles')->latest()->paginate($this->perPage());

        return response()->json($permissions, 200);
    }

    /**
     * Create permission
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('manage-permissions');

        $fields = $request->validate([
            'name' => 'required|string|max:255|unique:permissions',
        ]);

        $permission = Permission::create($fields);

        return response()->json($permission, 201);
    }

    /**
     * Get permission
     */
    public function show(Permission $permission): JsonResponse
    {
        Gate::authorize('manage-permissions');

        $permission->load('roles');

        return response()->json($permission, 200);
    }

    /**
     * Update permission
     */
    public function update(Request $request, Permission $permission): JsonResponse
    {
        Gate::authorize('manage-permissions');

        $fields = $request->validate([
            'name' => 'string|max:255|unique:permissions,name,'.$permission->id,
        ]);

        $permission->fill($fields)->save();

        return response()->json($permission, 200);
    }

    /**
     * Delete permission
     */
    public function destroy(Permission $permission): JsonResponse
    {
        Gate::authorize('manage-permissions');

        Permission::destroy($permission->id);

        return response()->json(['message' => 'Permission deleted.'], 200);
    }

    /**
     * Permanently delete permission
     */
    public function forceDestroy(Permission $permission): JsonResponse
    {
        return $this->performForceDestroy($permission, 'manage-permissions');
    }
}
