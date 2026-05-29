<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@inventory.api'],
            ['name' => 'Admin', 'password' => 'admin'],
        );
        $adminRole = Role::whereName('admin')->first();
        if (! $admin->roles()->where('role_id', $adminRole->id)->exists()) {
            $admin->roles()->attach($adminRole);
        }

        User::firstOrCreate(
            ['email' => 'demo@company.com'],
            ['name' => 'Demo User', 'password' => 'password'],
        );

        $electronics = Category::firstOrCreate(
            ['name' => 'Electronics'],
            ['description' => 'Gadgets and devices'],
        );
        $clothing = Category::firstOrCreate(
            ['name' => 'Clothing'],
            ['description' => 'Apparel and accessories'],
        );

        Product::firstOrCreate(
            ['sku' => 'ELEC-001'],
            ['name' => 'Wireless Headphones', 'price' => 79.99, 'stock' => 50, 'category_id' => $electronics->id],
        );

        Product::firstOrCreate(
            ['sku' => 'ELEC-002'],
            ['name' => 'USB-C Hub', 'price' => 34.99, 'stock' => 120, 'category_id' => $electronics->id],
        );

        $tshirt = Product::firstOrCreate(
            ['sku' => 'CLTH-001'],
            ['name' => 'Cotton T-Shirt', 'price' => 19.99, 'stock' => 200, 'category_id' => $clothing->id],
        );

        $headphones = Product::whereSku('ELEC-001')->first();
        $usbHub = Product::whereSku('ELEC-002')->first();

        foreach ([
            ['product_id' => $headphones->id, 'type' => 'in', 'quantity' => 50, 'before_quantity' => 0, 'after_quantity' => 50, 'reason' => 'Initial stock'],
            ['product_id' => $usbHub->id, 'type' => 'in', 'quantity' => 120, 'before_quantity' => 0, 'after_quantity' => 120, 'reason' => 'Initial stock'],
            ['product_id' => $tshirt->id, 'type' => 'in', 'quantity' => 200, 'before_quantity' => 0, 'after_quantity' => 200, 'reason' => 'Initial stock'],
        ] as $data) {
            StockMovement::firstOrCreate(
                ['product_id' => $data['product_id'], 'reason' => 'Initial stock'],
                $data,
            );
        }
    }
}
