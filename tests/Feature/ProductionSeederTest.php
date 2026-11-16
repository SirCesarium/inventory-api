<?php

use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Illuminate\Support\Facades\Hash;

describe('ProductionSeeder', function () {
    it('fails when admin credentials are not configured', function () {
        config(['admin.email' => null, 'admin.password' => null]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ADMIN_EMAIL and ADMIN_PASSWORD');

        $this->seed(ProductionSeeder::class);
    });

    it('creates the admin user from configured credentials', function () {
        config([
            'admin.email' => 'owner@example.com',
            'admin.password' => 'secret-password',
        ]);

        $this->seed(ProductionSeeder::class);

        $user = User::whereEmail('owner@example.com')->firstOrFail();

        expect($user->hasRole('admin'))->toBeTrue()
            ->and(Hash::check('secret-password', $user->password))->toBeTrue();
    });
});
