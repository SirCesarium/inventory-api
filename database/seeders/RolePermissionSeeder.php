<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
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
        ])->map(fn ($name) => Permission::firstOrCreate(compact('name')));

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'manager']);
        Role::firstOrCreate(['name' => 'employee']);

        $manager = Role::whereName('manager')->first();
        $perms = $permissions->whereIn('name', ['manage-products', 'manage-categories', 'view-audits'])->pluck('id');
        $existing = $manager->permissions()->pluck('permission_id');
        $manager->permissions()->attach($perms->diff($existing));
    }
}
