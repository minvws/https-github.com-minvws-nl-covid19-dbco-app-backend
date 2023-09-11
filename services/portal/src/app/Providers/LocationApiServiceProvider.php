<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Location\LocationClient;
use Illuminate\Support\ServiceProvider;

use function config;

class LocationApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(LocationClient::class, static function () {
            return new LocationClient([
                'base_uri' => config('services.location.base_uri'),
                'timeout' => 3.0,
            ]);
        });
    }
}
