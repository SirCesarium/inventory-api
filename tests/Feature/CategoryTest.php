<?php

use App\Models\Category;
use App\Models\Product;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

describe('Categories', function () {
    describe('list and show (no special permission)', function () {
        it('admin can list categories', function () {
            [, $token] = loginAsAdmin();

            $response = getJson('/api/categories', authHeaders($token));

            $response->assertStatus(200);
        });

        it('employee can list categories', function () {
            [, $token] = loginAsEmployee();

            getJson('/api/categories', authHeaders($token))->assertStatus(200);
        });

        it('employee can show category', function () {
            [, $token] = loginAsEmployee();
            $category = Category::create(['name' => 'Books']);

            $response = getJson("/api/categories/{$category->id}", authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['name' => 'Books']);
        });
    });

    describe('admin', function () {
        it('can create a category', function () {
            [, $token] = loginAsAdmin();

            $response = postJson('/api/categories', [
                'name' => 'Test Category',
                'description' => 'Test items',
            ], authHeaders($token));

            $response->assertStatus(201)
                ->assertJson([
                    'name' => 'Test Category',
                    'description' => 'Test items',
                ]);
        });

        it('cannot create category with duplicate name', function () {
            [, $token] = loginAsAdmin();
            $name = 'Unique-'.uniqid();
            Category::create(['name' => $name]);

            $response = postJson('/api/categories', [
                'name' => $name,
            ], authHeaders($token));

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });

        it('cannot create category without name', function () {
            [, $token] = loginAsAdmin();

            $response = postJson('/api/categories', [
                'description' => 'Just desc',
            ], authHeaders($token));

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
        });

        it('can update a category', function () {
            [, $token] = loginAsAdmin();
            $category = Category::create(['name' => 'Old Name']);

            $response = putJson("/api/categories/{$category->id}", [
                'name' => 'New Name',
                'description' => 'Updated desc',
            ], authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['name' => 'New Name', 'description' => 'Updated desc']);
        });

        it('can delete a category without products', function () {
            [, $token] = loginAsAdmin();
            $category = Category::create(['name' => 'Empty Cat']);

            $response = deleteJson("/api/categories/{$category->id}", [], authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['message' => 'Category deleted.']);
            expect(Category::query()->find($category->id))->toBeNull();
        });

        it('cannot delete a category with products', function () {
            [, $token] = loginAsAdmin();
            $category = Category::create(['name' => 'Full Cat']);
            Product::create([
                'sku' => 'SKU-001',
                'name' => 'Test Product',
                'price' => 10.99,
                'category_id' => $category->id,
            ]);

            $response = deleteJson("/api/categories/{$category->id}", [], authHeaders($token));

            $response->assertStatus(409)
                ->assertJson(['message' => 'Category has associated products.']);
            expect(Category::query()->find($category->id))->not->toBeNull();
        });
    });

    describe('authorization', function () {
        it('employee cannot create categories', function () {
            [, $token] = loginAsEmployee();

            postJson('/api/categories', ['name' => 'Test'], authHeaders($token))
                ->assertStatus(403);
        });

        it('employee cannot update categories', function () {
            [, $token] = loginAsEmployee();
            $category = Category::create(['name' => 'Test']);

            putJson("/api/categories/{$category->id}", ['name' => 'Updated'], authHeaders($token))
                ->assertStatus(403);
        });

        it('employee cannot delete categories', function () {
            [, $token] = loginAsEmployee();
            $category = Category::create(['name' => 'Test']);

            deleteJson("/api/categories/{$category->id}", [], authHeaders($token))
                ->assertStatus(403);
        });

        it('manager can create categories', function () {
            [, $token] = loginAsManager();

            postJson('/api/categories', ['name' => 'Manager Cat'], authHeaders($token))
                ->assertStatus(201);
        });
    });
});
