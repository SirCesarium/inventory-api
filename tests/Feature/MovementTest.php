<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

describe('Stock Movements', function () {
    describe('viewing movements', function () {
        it('admin can list movements', function () {
            [, $token] = loginAsAdmin();

            $response = getJson('/api/movements', authHeaders($token));

            $response->assertStatus(200);
        });

        it('admin can show a movement', function () {
            [, $token] = loginAsAdmin();

            $movement = StockMovement::query()->first();

            $response = getJson("/api/movements/{$movement->id}", authHeaders($token));

            $response->assertStatus(200)
                ->assertJson([
                    'type' => 'in',
                    'after_quantity' => 50,
                ]);
        });

        it('manager can list movements', function () {
            [, $token] = loginAsManager();

            getJson('/api/movements', authHeaders($token))->assertStatus(200);
        });
    });

    describe('creating movements', function () {
        it('manager can register an inbound movement', function () {
            [, $token] = loginAsManager();
            $product = Product::query()->with('lastMovement')->first();

            $beforeStock = $product->stock;

            $response = postJson("/api/products/{$product->id}/movements", [
                'type' => 'in',
                'quantity' => 10,
                'reason' => 'Restock',
            ], authHeaders($token));

            $response->assertStatus(201)
                ->assertJson([
                    'type' => 'in',
                    'quantity' => 10,
                    'before_quantity' => $beforeStock,
                    'after_quantity' => $beforeStock + 10,
                    'reason' => 'Restock',
                ]);
            expect($product->fresh()->stock)->toBe($beforeStock + 10);
        });

        it('manager can register an outbound movement with sufficient stock', function () {
            [, $token] = loginAsManager();
            $category = Category::create(['name' => 'Test']);
            $product = Product::create([
                'sku' => 'OUT-TEST',
                'name' => 'Out Test',
                'price' => 10,
                'category_id' => $category->id,
            ]);
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity' => 100,
                'before_quantity' => 0,
                'after_quantity' => 100,
                'reason' => 'Test setup',
            ]);

            $response = postJson("/api/products/{$product->id}/movements", [
                'type' => 'out',
                'quantity' => 30,
                'reason' => 'Sale',
            ], authHeaders($token));

            $response->assertStatus(201)
                ->assertJson([
                    'type' => 'out',
                    'quantity' => 30,
                    'before_quantity' => 100,
                    'after_quantity' => 70,
                ]);
            expect($product->fresh()->stock)->toBe(70);
        });

        it('manager cannot register an outbound movement with insufficient stock', function () {
            [, $token] = loginAsManager();
            $category = Category::create(['name' => 'Test']);
            $product = Product::create([
                'sku' => 'OUT-FAIL',
                'name' => 'Out Fail',
                'price' => 10,
                'category_id' => $category->id,
            ]);
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity' => 5,
                'before_quantity' => 0,
                'after_quantity' => 5,
                'reason' => 'Test setup',
            ]);

            $response = postJson("/api/products/{$product->id}/movements", [
                'type' => 'out',
                'quantity' => 10,
                'reason' => 'Oversell',
            ], authHeaders($token));

            $response->assertStatus(409)
                ->assertJson(['available' => 5]);
            expect($product->fresh()->stock)->toBe(5);
        });

        it('manager can register an adjustment', function () {
            [, $token] = loginAsManager();
            $category = Category::create(['name' => 'Test']);
            $product = Product::create([
                'sku' => 'ADJ-TEST',
                'name' => 'Adj Test',
                'price' => 10,
                'category_id' => $category->id,
            ]);
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity' => 50,
                'before_quantity' => 0,
                'after_quantity' => 50,
                'reason' => 'Test setup',
            ]);

            $response = postJson("/api/products/{$product->id}/movements", [
                'type' => 'adjustment',
                'quantity' => 25,
                'reason' => 'Inventory count correction',
            ], authHeaders($token));

            $response->assertStatus(201)
                ->assertJson([
                    'type' => 'adjustment',
                    'quantity' => 25,
                    'before_quantity' => 50,
                    'after_quantity' => 25,
                ]);
            expect($product->fresh()->stock)->toBe(25);
        });
    });

    describe('authorization', function () {
        it('employee cannot list movements', function () {
            [, $token] = loginAsEmployee();

            getJson('/api/movements', authHeaders($token))->assertStatus(403);
        });

        it('employee cannot create a movement', function () {
            [, $token] = loginAsEmployee();
            $product = Product::query()->first();

            postJson("/api/products/{$product->id}/movements", [
                'type' => 'in',
                'quantity' => 5,
            ], authHeaders($token))->assertStatus(403);
        });

        it('unauthenticated user cannot access movements', function () {
            getJson('/api/movements')->assertStatus(401);
        });
    });

    describe('validation', function () {
        it('rejects invalid type', function () {
            [, $token] = loginAsManager();
            $product = Product::query()->first();

            postJson("/api/products/{$product->id}/movements", [
                'type' => 'transfer',
                'quantity' => 1,
            ], authHeaders($token))->assertStatus(422);
        });

        it('rejects quantity less than 1', function () {
            [, $token] = loginAsManager();
            $product = Product::query()->first();

            postJson("/api/products/{$product->id}/movements", [
                'type' => 'in',
                'quantity' => 0,
            ], authHeaders($token))->assertStatus(422);
        });
    });
});
