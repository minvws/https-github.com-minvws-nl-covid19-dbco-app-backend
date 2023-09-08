<?php

declare(strict_types=1);

namespace App\Providers;

use App\Prometheus\Storage\Redis;
use Arquivei\LaravelPrometheusExporter\CollectorInterface;
use Arquivei\LaravelPrometheusExporter\MetricsController;
use Arquivei\LaravelPrometheusExporter\PrometheusExporter;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Predis\Client as PredisClient;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Adapter;

use function assert;
use function config;
use function is_array;
use function is_string;

final class PrometheusServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (!config('prometheus.metrics_route_enabled')) {
            return;
        }

        $path = config('prometheus.metrics_route_path');
        assert(is_string($path));

        Route::get(
            $path,
            [
                'as' => 'metrics',
                'uses' => MetricsController::class . '@getMetrics',
            ]
        );
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/prometheus.php', 'prometheus');

        // override exporter so we can disable the default metrics
        $this->app->singleton(PrometheusExporter::class, function ($app) {
            $adapter = $app['prometheus.storage_adapter'];
            $prometheus = new CollectorRegistry($adapter, false);

            $namespace = config('prometheus.namespace');
            assert(is_string($namespace));

            $collectorClasses = config('prometheus.collectors', []);
            assert(is_array($collectorClasses));

            $exporter = new PrometheusExporter($namespace, $prometheus);
            foreach ($collectorClasses as $collectorClass) {
                assert(is_string($collectorClass));

                $collector = $this->app->make($collectorClass);
                assert($collector instanceof CollectorInterface);

                $exporter->registerCollector($collector);
            }

            return $exporter;
        });

        $this->app->alias(PrometheusExporter::class, 'prometheus');

        // override storage adapter so we can use Predis
        $this->app->bind(Adapter::class, function () {
            /** @var RedisManager $redisManager */
            $redisManager = $this->app->get(RedisManager::class);
            $client = $redisManager->connection('prometheus')->client();
            assert($client instanceof PredisClient);
            return Redis::forConnection($client);
        });

        $this->app->alias(Adapter::class, 'prometheus.storage_adapter');
    }
}
