<?php

namespace App\Providers;

use App\Contracts\SmartOlt\SmartOltClientInterface;
use App\Services\SmartOlt\Clients\SmartOltHttpClient;
use App\Services\SmartOlt\Clients\SmartOltMockClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmartOltClientInterface::class, function ($app) {
            $mode = config('services.smartolt.mode', 'development');
            $mockMode = (bool) config('services.smartolt.mock_mode', true);
            $token = config('services.smartolt.api_token') ?: config('services.smartolt.api_key');

            if ($mode === 'production' && ! $mockMode && ! empty($token)) {
                return $app->make(SmartOltHttpClient::class);
            }

            return $app->make(SmartOltMockClient::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
