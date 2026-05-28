<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $permissions = collect([
            'manage-products',
            'manage-categories',
            'manage-users',
            'manage-roles',
            'manage-permissions',
            'view-audits',
        ])->map(fn ($name) => Permission::create(compact('name')));

        $admin = Role::create(['name' => 'admin']);
        $manager = Role::create(['name' => 'manager']);
        $employee = Role::create(['name' => 'employee']);

        $manager->permissions()->attach(
            $permissions->whereIn('name', ['manage-products', 'manage-categories', 'view-audits'])->pluck('id')
        );

        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@inventory.api',
            'password' => 'admin',
        ]);

        $user->roles()->attach($admin->id);
    }
}
