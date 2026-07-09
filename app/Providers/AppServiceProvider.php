<?php

namespace App\Providers;

use App\Domains\User\Enums\RoleType;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
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
        // bypass semua permission untuk role superadmin
        Gate::before(function ($user, $ability) {
            return $user->hasRole(RoleType::SUPERADMIN->value) ? true : null;
        });

        // Paksa HTTPS hanya jika APP_URL diatur ke https (misal saat pakai Ngrok) atau di Production
        if (str_contains(config('app.url'), 'https://') || app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
