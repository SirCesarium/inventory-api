<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * List users
     */
    public function index(): JsonResponse
    {
        Gate::authorize('users.read');

        $users = User::with('roles')->latest()->paginate($this->perPage());

        return response()->json($users, 200);
    }

    /**
     * Create user
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('users.create');

        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => $fields['email'],
        ]);

        if ($roleName = $fields['role'] ?? null) {
            $role = Role::whereName($roleName)->first();
            $user->roles()->attach($role);
        }

        return response()->json([
            'message' => 'User created.',
            'temporary_password' => $fields['email'],
            'user' => $user->load('roles'),
        ], 201);
    }

    /**
     * Get user
     */
    public function show(User $user): JsonResponse
    {
        Gate::authorize('users.read');

        $user->load('roles');

        return response()->json($user, 200);
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        Gate::authorize('users.update');

        $fields = $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,'.$user->id,
        ]);

        $user->fill($fields)->save();

        return response()->json($user, 200);
    }

    /**
     * Delete user
     */
    public function destroy(User $user): JsonResponse
    {
        Gate::authorize('users.delete');

        if ($user->email === 'admin@inventory.api') {
            return response()->json(['message' => 'Cannot delete admin user.'], 409);
        }

        User::destroy($user->id);

        return response()->json(['message' => 'User deleted.'], 200);
    }

    /**
     * Assign role to user
     */
    public function attachRole(User $user, Role $role): JsonResponse
    {
        Gate::authorize('users.update');

        $user->roles()->syncWithoutDetaching($role->id);

        return response()->json(['message' => "Role '{$role->name}' assigned."], 200);
    }

    /**
     * Remove role from user
     */
    public function detachRole(User $user, Role $role): JsonResponse
    {
        Gate::authorize('users.update');

        $user->roles()->detach($role->id);

        return response()->json(['message' => "Role '{$role->name}' removed."], 200);
    }

    /**
     * Permanently delete user
     */
    public function forceDestroy(User $user): JsonResponse
    {
        if ($user->email === 'admin@inventory.api') {
            return response()->json(['message' => 'Cannot delete admin user.'], 409);
        }

        return $this->performForceDestroy($user, 'users.delete');
    }
}
