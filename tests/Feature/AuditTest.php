<?php

use App\Models\Audit;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

describe('Audits', function () {
    describe('viewing audits', function () {
        it('admin can list audits', function () {
            [, $token] = loginAsAdmin();

            $response = getJson('/api/audits', authHeaders($token));

            $response->assertStatus(200);
        });

        it('admin can show an audit', function () {
            [$user, $token] = loginAsAdmin();
            $category = Category::create(['name' => 'Test Cat']);
            $audit = Audit::query()->where('auditable_id', $category->id)
                ->where('auditable_type', Category::class)
                ->first();

            $response = getJson("/api/audits/{$audit->id}", authHeaders($token));

            $response->assertStatus(200)
                ->assertJson([
                    'action' => 'create',
                    'auditable_id' => $category->id,
                ]);
        });

        it('manager can list audits', function () {
            [, $token] = loginAsManager();

            getJson('/api/audits', authHeaders($token))->assertStatus(200);
        });
    });

    describe('authorization', function () {
        it('employee cannot list audits', function () {
            [, $token] = loginAsEmployee();

            getJson('/api/audits', authHeaders($token))->assertStatus(403);
        });

        it('user without role cannot view audits', function () {
            [, $token] = loginAs();

            getJson('/api/audits', authHeaders($token))->assertStatus(403);
        });
    });

    describe('audit creation', function () {
        it('creates audit on product create', function () {
            [$user, $token] = loginAsAdmin();
            $category = Category::create(['name' => 'Cat']);

            postJson('/api/products', [
                'sku' => 'SKU-AUDIT',
                'name' => 'Audit Product',
                'price' => 5.00,
                'category_id' => $category->id,
            ], authHeaders($token))->assertStatus(201);

            $count = Audit::query()->where('action', 'create')
                ->where('auditable_type', Product::class)
                ->where('user_id', $user->id)
                ->count('*');
            expect($count)->toBe(1);
        });

        it('creates audit on category update', function () {
            [$user, $token] = loginAsAdmin();
            $category = Category::create(['name' => 'Original']);

            putJson("/api/categories/{$category->id}", [
                'name' => 'Updated',
            ], authHeaders($token))->assertStatus(200);

            $count = Audit::query()->where('action', 'update')
                ->where('auditable_type', Category::class)
                ->where('auditable_id', $category->id)
                ->where('user_id', $user->id)
                ->count('*');
            expect($count)->toBe(1);
        });

        it('creates audit on permission delete', function () {
            [$user, $token] = loginAsAdmin();
            $perm = Permission::create(['name' => 'temp-perm']);

            deleteJson("/api/permissions/{$perm->id}", [], authHeaders($token))->assertStatus(200);

            $count = Audit::query()->where('action', 'delete')
                ->where('auditable_type', Permission::class)
                ->where('auditable_id', $perm->id)
                ->where('user_id', $user->id)
                ->count('*');
            expect($count)->toBe(1);
        });

        it('creates audit on user creation via endpoint', function () {
            [$user, $token] = loginAsAdmin();

            postJson('/api/users', [
                'name' => 'New User',
                'email' => 'new@example.com',
            ], authHeaders($token))->assertStatus(201);

            $count = Audit::query()->where('action', 'create')
                ->where('auditable_type', User::class)
                ->where('user_id', $user->id)
                ->count('*');
            expect($count)->toBe(1);
        });

        it('creates audit on role create via endpoint', function () {
            [$user, $token] = loginAsAdmin();

            postJson('/api/roles', ['name' => 'new-role'], authHeaders($token))->assertStatus(201);

            $count = Audit::query()->where('action', 'create')
                ->where('auditable_type', Role::class)
                ->where('user_id', $user->id)
                ->count('*');
            expect($count)->toBe(1);
        });
    });
});
