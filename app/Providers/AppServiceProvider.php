<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            $user->loadMissing('roles.permissions');

            foreach ($user->roles as $role) {
                if ($role->name === 'admin') {
                    return true;
                }

                foreach ($role->permissions as $permission) {
                    if ($permission->name === $ability) {
                        return true;
                    }
                }
            }

            return null;
        });
    }
}
