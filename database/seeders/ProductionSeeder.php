<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

class ProductionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (blank($email) || blank($password)) {
            throw new RuntimeException(
                'ADMIN_EMAIL and ADMIN_PASSWORD must be set before running ProductionSeeder.'
            );
        }

        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => $email,
            'password' => $password,
        ]);

        $user->roles()->attach(Role::whereName('admin')->first());
    }
}
