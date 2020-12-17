<?php

namespace App\Providers;

use App\Security\ProxySecurityCache;
use App\Security\RedisSecurityCache;
use App\Security\SecurityCache;
use Illuminate\Support\ServiceProvider;

class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            SecurityCache::class,
            fn ($app) => new ProxySecurityCache(new RedisSecurityCache($app->make('redis.connection')))
        );
    }
}
