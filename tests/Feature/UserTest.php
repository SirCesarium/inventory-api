<?php

use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

describe('Users', function () {
    describe('admin', function () {
        it('can list users', function () {
            [, $token] = loginAsAdmin();
            User::factory()->count(3)->create();

            $response = getJson('/api/users', authHeaders($token));

            $response->assertStatus(200)
                ->assertJsonCount(4, 'data');
        });

        it('can create a user without role', function () {
            [, $token] = loginAsAdmin();

            $response = postJson('/api/users', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ], authHeaders($token));

            $response->assertStatus(201)
                ->assertJson([
                    'message' => 'User created.',
                    'temporary_password' => 'john@example.com',
                ]);
            expect(User::whereEmail('john@example.com')->exists())->toBeTrue();
        });

        it('can create a user with role', function () {
            [, $token] = loginAsAdmin();

            $response = postJson('/api/users', [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'role' => 'manager',
            ], authHeaders($token));

            $response->assertStatus(201);
            $user = User::whereEmail('jane@example.com')->first();
            expect($user->roles->pluck('name'))->toContain('manager');
        });

        it('cannot create user with duplicate email', function () {
            [, $token] = loginAsAdmin();

            postJson('/api/users', [
                'name' => 'John',
                'email' => 'john@example.com',
            ], authHeaders($token))->assertStatus(201);

            $response = postJson('/api/users', [
                'name' => 'John Dup',
                'email' => 'john@example.com',
            ], authHeaders($token));

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        it('cannot create user with non-existent role', function () {
            [, $token] = loginAsAdmin();

            $response = postJson('/api/users', [
                'name' => 'John',
                'email' => 'john@example.com',
                'role' => 'nonexistent',
            ], authHeaders($token));

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['role']);
        });

        it('cannot create user without name', function () {
            [, $token] = loginAsAdmin();

            $response = postJson('/api/users', [
                'email' => 'john@example.com',
            ], authHeaders($token));

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });

        it('can show a user with roles', function () {
            [, $token] = loginAsAdmin();
            $user = User::factory()->create();

            $response = getJson("/api/users/{$user->id}", authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['name' => $user->name, 'email' => $user->email]);
        });

        it('can update a user', function () {
            [, $token] = loginAsAdmin();
            $user = User::factory()->create();

            $response = putJson("/api/users/{$user->id}", [
                'name' => 'Updated Name',
            ], authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['name' => 'Updated Name']);
        });

        it('can delete a user', function () {
            [, $token] = loginAsAdmin();
            $user = User::factory()->create();

            $response = deleteJson("/api/users/{$user->id}", [], authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['message' => 'User deleted.']);
            expect(User::query()->find($user->id))->toBeNull();
        });

        it('can attach role to user', function () {
            [, $token] = loginAsAdmin();
            $user = User::factory()->create();
            $role = Role::whereName('manager')->first();

            $response = postJson("/api/users/{$user->id}/roles/{$role->id}", [], authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['message' => "Role '{$role->name}' assigned."]);
            expect($user->fresh()->roles)->toHaveCount(1);
        });

        it('can detach role from user', function () {
            [, $token] = loginAsAdmin();
            $user = User::factory()->create();
            $role = Role::whereName('manager')->first();
            $user->roles()->attach($role);

            $response = deleteJson("/api/users/{$user->id}/roles/{$role->id}", [], authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['message' => "Role '{$role->name}' removed."]);
            expect($user->fresh()->roles)->toHaveCount(0);
        });

        it('can attach same role twice without error', function () {
            [, $token] = loginAsAdmin();
            $user = User::factory()->create();
            $role = Role::whereName('manager')->first();

            postJson("/api/users/{$user->id}/roles/{$role->id}", [], authHeaders($token))->assertStatus(200);
            postJson("/api/users/{$user->id}/roles/{$role->id}", [], authHeaders($token))->assertStatus(200);

            expect($user->fresh()->roles)->toHaveCount(1);
        });
    });

    describe('authorization', function () {
        it('manager cannot list users', function () {
            [, $token] = loginAsManager();
            getJson('/api/users', authHeaders($token))->assertStatus(403);
        });

        it('employee cannot create users', function () {
            [, $token] = loginAsEmployee();
            postJson('/api/users', [
                'name' => 'Test',
                'email' => 'test@example.com',
            ], authHeaders($token))->assertStatus(403);
        });
    });
});
