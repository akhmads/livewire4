<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Super admin bypass - dapat akses semua
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }

            // Penting: return null agar Gate lanjut ke Spatie/laravel-permission
            return null;
        });
    }
}
