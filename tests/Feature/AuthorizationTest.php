<?php

use App\Models\Category;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

describe('RBAC Authorization', function () {
    describe('admin bypass (can do everything)', function () {
        it('admin can manage products', function () {
            [, $token] = loginAsAdmin();
            $category = Category::create(['name' => 'Cat']);

            $r1 = postJson('/api/products', [
                'sku' => 'SKU-001', 'name' => 'P1', 'price' => 1, 'category_id' => $category->id,
            ], authHeaders($token));
            $r1->assertStatus(201);

            $product = $r1->json();
            putJson("/api/products/{$product['id']}", ['name' => 'P2'], authHeaders($token))->assertStatus(200);
            deleteJson("/api/products/{$product['id']}", [], authHeaders($token))->assertStatus(200);
        });

        it('admin can manage categories', function () {
            [, $token] = loginAsAdmin();

            $r1 = postJson('/api/categories', ['name' => 'Cat'], authHeaders($token));
            $r1->assertStatus(201);

            $cat = $r1->json();
            putJson("/api/categories/{$cat['id']}", ['name' => 'Updated'], authHeaders($token))->assertStatus(200);
            deleteJson("/api/categories/{$cat['id']}", [], authHeaders($token))->assertStatus(200);
        });

        it('admin can manage users', function () {
            [, $token] = loginAsAdmin();

            getJson('/api/users', authHeaders($token))->assertStatus(200);
            postJson('/api/users', ['name' => 'U', 'email' => 'u@test.com'], authHeaders($token))->assertStatus(201);
        });

        it('admin can manage roles', function () {
            [, $token] = loginAsAdmin();

            getJson('/api/roles', authHeaders($token))->assertStatus(200);
            postJson('/api/roles', ['name' => 'custom-role'], authHeaders($token))->assertStatus(201);
        });

        it('admin can manage permissions', function () {
            [, $token] = loginAsAdmin();

            getJson('/api/permissions', authHeaders($token))->assertStatus(200);
            getJson('/api/permissions/available', authHeaders($token))->assertStatus(200);
        });

        it('admin can view audits', function () {
            [, $token] = loginAsAdmin();

            getJson('/api/audits', authHeaders($token))->assertStatus(200);
        });
    });

    describe('manager permissions', function () {
        it('manager can manage products', function () {
            [, $token] = loginAsManager();
            $category = Category::create(['name' => 'Cat']);

            postJson('/api/products', [
                'sku' => 'SKU-MGR', 'name' => 'Mgr', 'price' => 1, 'category_id' => $category->id,
            ], authHeaders($token))->assertStatus(201);
        });

        it('manager can manage categories', function () {
            [, $token] = loginAsManager();

            postJson('/api/categories', ['name' => 'Mgr Cat'], authHeaders($token))->assertStatus(201);
        });

        it('manager can view audits', function () {
            [, $token] = loginAsManager();

            getJson('/api/audits', authHeaders($token))->assertStatus(200);
        });

        it('manager cannot manage users', function () {
            [, $token] = loginAsManager();

            getJson('/api/users', authHeaders($token))->assertStatus(403);
            postJson('/api/users', ['name' => 'U', 'email' => 'u@test.com'], authHeaders($token))->assertStatus(403);
        });

        it('manager cannot manage roles', function () {
            [, $token] = loginAsManager();

            getJson('/api/roles', authHeaders($token))->assertStatus(403);
            postJson('/api/roles', ['name' => 'new-role'], authHeaders($token))->assertStatus(403);
        });

        it('manager cannot manage permissions', function () {
            [, $token] = loginAsManager();

            getJson('/api/permissions', authHeaders($token))->assertStatus(403);
        });
    });

    describe('employee permissions', function () {
        it('employee can list products', function () {
            [, $token] = loginAsEmployee();

            getJson('/api/products', authHeaders($token))->assertStatus(200);
        });

        it('employee can list categories', function () {
            [, $token] = loginAsEmployee();

            getJson('/api/categories', authHeaders($token))->assertStatus(200);
        });

        it('employee cannot create products', function () {
            [, $token] = loginAsEmployee();
            $category = Category::create(['name' => 'Cat']);

            postJson('/api/products', [
                'sku' => 'SKU-001', 'name' => 'Test', 'price' => 1, 'category_id' => $category->id,
            ], authHeaders($token))->assertStatus(403);
        });

        it('employee cannot create categories', function () {
            [, $token] = loginAsEmployee();

            postJson('/api/categories', ['name' => 'Test'], authHeaders($token))->assertStatus(403);
        });

        it('employee cannot view audits', function () {
            [, $token] = loginAsEmployee();

            getJson('/api/audits', authHeaders($token))->assertStatus(403);
        });
    });

    describe('unauthenticated access', function () {
        it('cannot access any protected route without token', function () {
            getJson('/api/users')->assertStatus(401);
            getJson('/api/roles')->assertStatus(401);
            getJson('/api/permissions')->assertStatus(401);
            getJson('/api/categories')->assertStatus(401);
            getJson('/api/products')->assertStatus(401);
            getJson('/api/audits')->assertStatus(401);
            getJson('/api/me')->assertStatus(401);
        });
    });
});
