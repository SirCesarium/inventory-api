<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * Register a new user (available only for admins)
     */
    public function store(Request $request)
    {
        Gate::authorize('create-users');

        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => $fields['email'],
        ]);

        return response()->json([
            'message' => 'User created.',
            'temporary_password' => $fields['email'],
            'user' => $user
        ], 201);
    }
}
