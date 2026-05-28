<?php

use App\Models\Permission;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

describe('Permissions', function () {
    describe('admin', function () {
        it('can list permissions', function () {
            [, $token] = loginAsAdmin();
            Permission::create(['name' => 'extra-perm']);

            $response = getJson('/api/permissions', authHeaders($token));

            $response->assertStatus(200)
                ->assertJsonCount(7, 'data');
        });

        it('can create a permission', function () {
            [, $token] = loginAsAdmin();

            $response = postJson('/api/permissions', [
                'name' => 'manage-reports',
            ], authHeaders($token));

            $response->assertStatus(201)
                ->assertJson(['name' => 'manage-reports']);
            expect(Permission::query()->where('name', 'manage-reports')->exists())->toBeTrue();
        });

        it('cannot create permission with duplicate name', function () {
            [, $token] = loginAsAdmin();
            Permission::create(['name' => 'manage-reports']);

            $response = postJson('/api/permissions', [
                'name' => 'manage-reports',
            ], authHeaders($token));

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });

        it('cannot create permission without name', function () {
            [, $token] = loginAsAdmin();

            $response = postJson('/api/permissions', [], authHeaders($token));

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });

        it('can show a permission', function () {
            [, $token] = loginAsAdmin();
            $permission = Permission::query()->first();

            $response = getJson("/api/permissions/{$permission->id}", authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['name' => $permission->name]);
        });

        it('can update a permission', function () {
            [, $token] = loginAsAdmin();
            $permission = Permission::query()->first();

            $response = putJson("/api/permissions/{$permission->id}", [
                'name' => 'updated-name',
            ], authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['name' => 'updated-name']);
        });

        it('can delete a permission', function () {
            [, $token] = loginAsAdmin();
            $permission = Permission::create(['name' => 'temp-perm']);

            $headers = authHeaders($token);
            $response = deleteJson("/api/permissions/{$permission->id}", [], $headers);

            $response->assertStatus(200)
                ->assertJson(['message' => 'Permission deleted.']);
            expect(Permission::query()->find($permission->id))->toBeNull();
        });
    });

    describe('authorization', function () {
        it('manager cannot list permissions', function () {
            [, $token] = loginAsManager();

            getJson('/api/permissions', authHeaders($token))->assertStatus(403);
        });

        it('manager cannot create permissions', function () {
            [, $token] = loginAsManager();

            postJson('/api/permissions', ['name' => 'new-perm'], authHeaders($token))
                ->assertStatus(403);
        });

        it('employee cannot list permissions', function () {
            [, $token] = loginAsEmployee();

            getJson('/api/permissions', authHeaders($token))->assertStatus(403);
        });
    });
});
