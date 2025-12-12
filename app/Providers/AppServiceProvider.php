<?php

namespace App\Providers;

use App\Services\Logging\LogInterface;
use App\Services\Logging\LogManager;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(maxAttempts: 60)
                ->by($request->user()?->id ?: $request->ip());
        });

        $this->app->singleton(LogInterface::class, function () {
            return new LogManager();
        });
    }
}
