<?php

use App\Models\Permission;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

describe('Permissions', function () {
    describe('admin', function () {
        it('can list permissions', function () {
            [, $token] = loginAsAdmin();
            Permission::create(['name' => 'extra-perm']);

            $response = getJson('/api/permissions', authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['total' => 25]);
        });

        it('can see available permissions from config', function () {
            [, $token] = loginAsAdmin();

            $response = getJson('/api/permissions/available', authHeaders($token));

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'products', 'categories', 'users', 'roles', 'permissions', 'audits',
                ]);
        });

        it('cannot create a permission (read-only)', function () {
            [, $token] = loginAsAdmin();

            postJson('/api/permissions', ['name' => 'new-perm'], authHeaders($token))
                ->assertStatus(405);
        });

        it('can show a permission', function () {
            [, $token] = loginAsAdmin();
            $permission = Permission::query()->first();

            $response = getJson("/api/permissions/{$permission->id}", authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['name' => $permission->name]);
        });
    });

    describe('authorization', function () {
        it('manager cannot list permissions', function () {
            [, $token] = loginAsManager();

            getJson('/api/permissions', authHeaders($token))->assertStatus(403);
        });

        it('employee cannot list permissions', function () {
            [, $token] = loginAsEmployee();

            getJson('/api/permissions', authHeaders($token))->assertStatus(403);
        });
    });
});
