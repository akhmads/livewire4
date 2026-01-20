<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Essential model configurations
        Model::preventLazyLoading(true);
        Model::unguard();

        // Database safety in production
        DB::prohibitDestructiveCommands(app()->isProduction());

        // Date handling
        Date::use(CarbonImmutable::class);

        // Vite optimization
        Vite::useAggressivePrefetching();

        // Force HTTPS in production
        if (app()->environment(['production'])) {
            URL::forceHttps();
            URL::forceScheme('https');
            request()->server->set('HTTPS', request()->header('X-Forwarded-Proto', 'https') == 'https' ? 'on' : 'off');
        }

        // Gate authorization
        Gate::before(function (User $user, string $ability) {
            return true;
        });

        // Commented out site meta configuration
        // if( ! app()->runningInConsole()) {
        //     config(['site.meta.description' => settings('meta_description')]);
        //     config(['site.meta.keyword' => settings('meta_keyword')]);
        // }
    }
}
