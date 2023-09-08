<?php

declare(strict_types=1);

namespace App\Providers;

use Ackintosh\Ganesha\Storage\Adapter\Redis;
use Ackintosh\Ganesha\Storage\AdapterInterface;
use App\Http\CircuitBreaker\CircuitBreaker;
use App\Http\CircuitBreaker\GaneshaCircuitBreaker;
use Illuminate\Foundation\Application;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\ServiceProvider;
use Predis\Client;

class CircuitBreakerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CircuitBreaker::class, GaneshaCircuitBreaker::class);

        $this->app->bind(AdapterInterface::class, static function (Application $app): Redis {
            /** @var RedisManager $redisManager */
            $redisManager = $app->get(RedisManager::class);

            /** @var Client $client */
            $client = $redisManager->connection('ganesha')->client();

            return new Redis($client);
        });
    }
}
