<?php

namespace App\Providers;

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
        // Paksa HTTPS hanya jika APP_URL diatur ke https (misal saat pakai Ngrok) atau di Production
        if (str_contains(config('app.url'), 'https://') || app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
