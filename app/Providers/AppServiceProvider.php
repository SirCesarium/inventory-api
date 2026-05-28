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
            if ($user->roles()->where('name', 'admin')->exists()) {
                return true;
            }

            return $user->roles()->whereHas('permissions', fn ($q) => $q->where('name', $ability))->exists() ?: null;
        });
    }
}
