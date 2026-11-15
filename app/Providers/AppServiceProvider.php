<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\SmartOlt\SmartOltClientInterface;
use App\Services\SmartOlt\Clients\SmartOltHttpClient;
use App\Services\SmartOlt\Clients\SmartOltMockClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SmartOltClientInterface::class, function ($app) {
            $mode = config('services.smartolt.mode', 'development');

            if ($mode === 'production') {
                return $app->make(SmartOltHttpClient::class);
            }

            return $app->make(SmartOltMockClient::class);
        });
    }

    public function boot(): void
    {
    }
}
