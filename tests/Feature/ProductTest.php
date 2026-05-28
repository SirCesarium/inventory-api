<?php

use App\Models\Category;
use App\Models\Product;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

describe('Products', function () {
    describe('list and show (no special permission)', function () {
        it('admin can list products', function () {
            [, $token] = loginAsAdmin();
            $category = Category::create(['name' => 'Gadgets']);
            Product::create([
                'sku' => 'SKU-001',
                'name' => 'Widget',
                'price' => 9.99,
                'category_id' => $category->id,
            ]);

            $response = getJson('/api/products', authHeaders($token));

            $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
        });

        it('employee can list products', function () {
            [, $token] = loginAsEmployee();

            getJson('/api/products', authHeaders($token))->assertStatus(200);
        });

        it('employee can show product', function () {
            [, $token] = loginAsEmployee();
            $category = Category::create(['name' => 'Gadgets']);
            $product = Product::create([
                'sku' => 'SKU-001',
                'name' => 'Widget',
                'price' => 9.99,
                'category_id' => $category->id,
            ]);

            $response = getJson("/api/products/{$product->id}", authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['name' => 'Widget']);
        });
    });

    describe('manager', function () {
        it('can create a product', function () {
            [, $token] = loginAsManager();
            $category = Category::create(['name' => 'Gadgets']);

            $response = postJson('/api/products', [
                'sku' => 'SKU-001',
                'name' => 'Widget',
                'price' => 9.99,
                'stock' => 10,
                'category_id' => $category->id,
            ], authHeaders($token));

            $response->assertStatus(201)
                ->assertJson([
                    'sku' => 'SKU-001',
                    'name' => 'Widget',
                    'price' => 9.99,
                    'stock' => 10,
                ]);
        });

        it('cannot create product with duplicate sku', function () {
            [, $token] = loginAsManager();
            $category = Category::create(['name' => 'Gadgets']);
            Product::create([
                'sku' => 'SKU-001',
                'name' => 'Old Product',
                'price' => 5.00,
                'category_id' => $category->id,
            ]);

            $response = postJson('/api/products', [
                'sku' => 'SKU-001',
                'name' => 'New Product',
                'price' => 10.00,
                'category_id' => $category->id,
            ], authHeaders($token));

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['sku']);
        });

        it('cannot create product without required fields', function () {
            [, $token] = loginAsManager();

            $response = postJson('/api/products', [], authHeaders($token));

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['sku', 'name', 'price', 'category_id']);
        });

        it('can update a product', function () {
            [, $token] = loginAsManager();
            $category = Category::create(['name' => 'Gadgets']);
            $product = Product::create([
                'sku' => 'SKU-001',
                'name' => 'Widget',
                'price' => 9.99,
                'stock' => 5,
                'category_id' => $category->id,
            ]);

            $response = putJson("/api/products/{$product->id}", [
                'name' => 'Super Widget',
                'price' => 19.99,
                'stock' => 20,
            ], authHeaders($token));

            $response->assertStatus(200)
                ->assertJson([
                    'name' => 'Super Widget',
                    'price' => 19.99,
                    'stock' => 20,
                ]);
        });

        it('can delete a product', function () {
            [, $token] = loginAsManager();
            $category = Category::create(['name' => 'Gadgets']);
            $product = Product::create([
                'sku' => 'SKU-001',
                'name' => 'Widget',
                'price' => 9.99,
                'category_id' => $category->id,
            ]);

            $response = deleteJson("/api/products/{$product->id}", [], authHeaders($token));

            $response->assertStatus(200)
                ->assertJson(['message' => 'Product deleted.']);
            expect(Product::query()->find($product->id))->toBeNull();
        });
    });

    describe('authorization', function () {
        it('employee cannot create products', function () {
            [, $token] = loginAsEmployee();
            $category = Category::create(['name' => 'Gadgets']);

            postJson('/api/products', [
                'sku' => 'SKU-001',
                'name' => 'Test',
                'price' => 1.00,
                'category_id' => $category->id,
            ], authHeaders($token))
                ->assertStatus(403);
        });

        it('employee cannot update products', function () {
            [, $token] = loginAsEmployee();
            $category = Category::create(['name' => 'Gadgets']);
            $product = Product::create([
                'sku' => 'SKU-001',
                'name' => 'Widget',
                'price' => 9.99,
                'category_id' => $category->id,
            ]);

            putJson("/api/products/{$product->id}", ['name' => 'Hacked'], authHeaders($token))
                ->assertStatus(403);
        });

        it('employee cannot delete products', function () {
            [, $token] = loginAsEmployee();
            $category = Category::create(['name' => 'Gadgets']);
            $product = Product::create([
                'sku' => 'SKU-001',
                'name' => 'Widget',
                'price' => 9.99,
                'category_id' => $category->id,
            ]);

            deleteJson("/api/products/{$product->id}", [], authHeaders($token))
                ->assertStatus(403);
        });

        it('admin can create products', function () {
            [, $token] = loginAsAdmin();
            $category = Category::create(['name' => 'Gadgets']);

            postJson('/api/products', [
                'sku' => 'SKU-ADMIN',
                'name' => 'Admin Product',
                'price' => 99.99,
                'category_id' => $category->id,
            ], authHeaders($token))
                ->assertStatus(201);
        });
    });
});
