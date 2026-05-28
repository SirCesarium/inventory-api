<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    /**
     * List roles
     */
    public function index(): JsonResponse
    {
        Gate::authorize('manage-roles');

        $roles = Role::with('permissions')->latest()->paginate($this->perPage());

        return response()->json($roles, 200);
    }

    /**
     * Create role
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('manage-roles');

        $fields = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
        ]);

        $role = Role::create($fields);

        return response()->json($role, 201);
    }

    /**
     * Get role
     */
    public function show(Role $role): JsonResponse
    {
        Gate::authorize('manage-roles');

        $role->load('permissions');

        return response()->json($role, 200);
    }

    /**
     * Update role
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        Gate::authorize('manage-roles');

        $fields = $request->validate([
            'name' => 'string|max:255|unique:roles,name,'.$role->id,
        ]);

        $role->fill($fields)->save();

        return response()->json($role, 200);
    }

    /**
     * Delete role
     */
    public function destroy(Role $role): JsonResponse
    {
        Gate::authorize('manage-roles');

        Role::destroy($role->id);

        return response()->json(['message' => 'Role deleted.'], 200);
    }

    /**
     * Assign permission to role
     */
    public function attachPermission(Role $role, Permission $permission): JsonResponse
    {
        Gate::authorize('manage-roles');

        $role->permissions()->syncWithoutDetaching($permission->id);

        return response()->json(['message' => "Permission '{$permission->name}' assigned."], 200);
    }

    /**
     * Remove permission from role
     */
    public function detachPermission(Role $role, Permission $permission): JsonResponse
    {
        Gate::authorize('manage-roles');

        $role->permissions()->detach($permission->id);

        return response()->json(['message' => "Permission '{$permission->name}' removed."], 200);
    }
}
