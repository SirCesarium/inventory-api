<?php

use App\Models\Permission;
use App\Models\Role;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

describe('Roles', function () {
    describe('admin', function () {
        it('can list roles', function () {
            [, $token] = loginAsAdmin();

            $response = getJson('/api/roles', authHeaders($token));

            $response->assertStatus(200)
                ->assertJsonCount(3, 'data');
        });

        it('can create a role', function () {
            [, $token] = loginAsAdmin();

            $response = postJson('/api/roles', [
                'name' => 'editor',
            ], authHeaders($token));

            $response->assertStatus(201)
                ->assertJson(['name' => 'editor']);
            expect(Role::whereName('editor')->exists())->toBeTrue();
        });

        it('cannot create role with duplicate name', function () {
            [, $token] = loginAsAdmin();

            $response = postJson('/api/roles', [
                'name' => 'admin',
            ], authHeaders($token));

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });

        it('cannot create role without name', function () {
            [, $token] = loginAsAdmin();

            $response = postJson('/api/roles', [], authHeaders($token));

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });

        it('can show a role with permissions', function () {
            [, $token] = loginAsAdmin();
            $role = Role::whereName('admin')->first();

            $response = getJson("/api/roles/{$role->id}", authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['name' => 'admin']);
        });

        it('can update a role', function () {
            [, $token] = loginAsAdmin();
            $role = Role::whereName('manager')->first();

            $response = putJson("/api/roles/{$role->id}", [
                'name' => 'supervisor',
            ], authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['name' => 'supervisor']);
        });

        it('can delete a role', function () {
            [, $token] = loginAsAdmin();
            $role = Role::create(['name' => 'temp-role']);

            $response = deleteJson("/api/roles/{$role->id}", [], authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['message' => 'Role deleted.']);
            expect(Role::query()->find($role->id))->toBeNull();
        });

        it('can attach permission to role', function () {
            [, $token] = loginAsAdmin();
            $role = Role::whereName('employee')->first();
            $perm = Permission::whereName('users.read')->first();

            $response = postJson("/api/roles/{$role->id}/permissions/{$perm->id}", [], authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['message' => "Permission '{$perm->name}' assigned."]);
            expect($role->fresh()->permissions)->toHaveCount(3);
        });

        it('can detach permission from role', function () {
            [, $token] = loginAsAdmin();
            $role = Role::whereName('manager')->first();
            $perm = Permission::whereName('products.read')->first();

            $response = deleteJson("/api/roles/{$role->id}/permissions/{$perm->id}", [], authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['message' => "Permission '{$perm->name}' removed."]);
            expect($role->fresh()->permissions)->toHaveCount(10);
        });

        it('can attach same permission twice without error', function () {
            [, $token] = loginAsAdmin();
            $role = Role::whereName('employee')->first();
            $perm = Permission::whereName('users.read')->first();
            postJson("/api/roles/{$role->id}/permissions/{$perm->id}", [], authHeaders($token))->assertStatus(200);
            postJson("/api/roles/{$role->id}/permissions/{$perm->id}", [], authHeaders($token))->assertStatus(200);

            expect($role->fresh()->permissions)->toHaveCount(3);
        });
    });

    describe('authorization', function () {
        it('manager cannot list roles', function () {
            [, $token] = loginAsManager();
            getJson('/api/roles', authHeaders($token))->assertStatus(403);
        });

        it('manager cannot create roles', function () {
            [, $token] = loginAsManager();
            postJson('/api/roles', ['name' => 'new-role'], authHeaders($token))->assertStatus(403);
        });

        it('employee cannot manage roles', function () {
            [, $token] = loginAsEmployee();
            $role = Role::whereName('employee')->first();

            getJson("/api/roles/{$role->id}", authHeaders($token))->assertStatus(403);
            putJson("/api/roles/{$role->id}", ['name' => 'new-name'], authHeaders($token))->assertStatus(403);
            deleteJson("/api/roles/{$role->id}", [], authHeaders($token))->assertStatus(403);
        });
    });
});
