<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function () {
        $this->seed(DatabaseSeeder::class);
    })
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function loginAsAdmin(): array
{
    $user = User::query()->where('email', 'admin@inventory.api')->firstOrFail();
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

function loginAsManager(): array
{
    $role = Role::query()->where('name', 'manager')->firstOrFail();
    $user = User::factory()->create();
    $user->roles()->attach($role);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

function loginAsEmployee(): array
{
    $role = Role::query()->where('name', 'employee')->firstOrFail();
    $user = User::factory()->create();
    $user->roles()->attach($role);
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

function loginAs(?string $roleName = null): array
{
    $user = User::factory()->create();
    if ($roleName) {
        $role = Role::query()->where('name', $roleName)->firstOrFail();
        $user->roles()->attach($role);
    }
    $token = $user->createToken('test')->plainTextToken;

    return [$user, $token];
}

function authHeaders(string $token): array
{
    return ['Authorization' => "Bearer $token"];
}
