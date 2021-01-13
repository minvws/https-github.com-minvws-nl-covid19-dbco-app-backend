<?php

namespace App\Providers;

use App\Providers\Auth\IdentityHubProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
        $this->bootIdentityHubSocialite();
    }

    private function bootIdentityHubSocialite()
    {
        $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
        $socialite->extend(
            'identityhub',
            function ($app) use ($socialite) {
                $config = $app['config']['services.identityhub'];
                return $socialite->buildProvider(IdentityHubProvider::class, $config);
            }
        );
    }
}
