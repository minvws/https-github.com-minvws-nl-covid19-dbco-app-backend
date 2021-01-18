<?php

namespace App\Providers;

use DBCO\Shared\Application\Metrics\Transformers\EventTransformer;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;
use MinVWS\HealthCheck\Checks\GuzzleHealthCheck;
use MinVWS\HealthCheck\Checks\PDOHealthCheck;
use MinVWS\HealthCheck\Checks\PredisHealthCheck;
use MinVWS\HealthCheck\HealthChecker;
use MinVWS\Metrics\Transformers\EventTransformer as EventTransformerInterface;
use PDO;

class MiscServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('privateAPIGuzzleClient', fn () => new GuzzleClient(config('services.private_api.client_options')));
        $this->app->bind('healthAuthorityAPIGuzzleClient', fn () => new GuzzleClient(config('services.healthauthority_api.client_options')));

        $this->app->bind(EventTransformerInterface::class, EventTransformer::class);
        $this->app->when(EventTransformer::class)
            ->needs(PDO::class)
            ->give(fn () => DB::connection()->getPdo());

        $this->app->bind(HealthChecker::class, function (Container $app) {
            $healthChecker = new HealthChecker();
            $healthChecker->addHealthCheck('redis-haa', new PredisHealthCheck(Redis::connection()->client()));
            $healthChecker->addHealthCheck('mysql', new PDOHealthCheck(DB::connection()->getPdo()));
            $healthChecker->addHealthCheck('private-api', new GuzzleHealthCheck($app->get('privateAPIGuzzleClient'), 'GET', 'ping'));
            $healthChecker->addHealthCheck('healthauthority-api', new GuzzleHealthCheck($app->get('healthAuthorityAPIGuzzleClient'), 'GET', 'ping'));
            return $healthChecker;
        });
    }
}
