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
        $permissionNames = collect(config('permissions'))
            ->flatMap(fn (array $actions, string $resource) => array_map(
                fn (string $action) => "{$resource}.{$action}",
                $actions,
            ))
            ->values();

        $permissions = $permissionNames->map(fn ($name) => Permission::firstOrCreate(compact('name')));

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'manager']);
        Role::firstOrCreate(['name' => 'employee']);

        $manager = Role::whereName('manager')->first();
        $managerPerms = $permissions->filter(fn (Permission $p) => str_starts_with($p->name, 'products.')
            || str_starts_with($p->name, 'categories.')
            || $p->name === 'audits.read'
        )->pluck('id');
        $existing = $manager->permissions()->pluck('permission_id');
        $manager->permissions()->attach($managerPerms->diff($existing));

        $employee = Role::whereName('employee')->first();
        $employeePerms = $permissions->filter(fn (Permission $p) => $p->name === 'products.read'
            || $p->name === 'categories.read'
        )->pluck('id');
        $existing = $employee->permissions()->pluck('permission_id');
        $employee->permissions()->attach($employeePerms->diff($existing));
    }
}
