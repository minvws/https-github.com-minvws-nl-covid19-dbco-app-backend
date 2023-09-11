<?php

declare(strict_types=1);

namespace App\Providers;

use App\Prometheus\Storage\Redis;
use App\Repositories\Metric\MetricRepository;
use App\Repositories\Metric\PrometheusRepository;
use Arquivei\LaravelPrometheusExporter\CollectorInterface;
use Arquivei\LaravelPrometheusExporter\MetricsController;
use Arquivei\LaravelPrometheusExporter\PrometheusExporter;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Predis\Client;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Adapter;
use RedisManager;

use function assert;
use function config;
use function is_array;
use function is_string;

/**
 * Overrides some Prometheus Laravel Exporter defaults which aren't configurable.
 */
class PrometheusServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (!config('prometheus.metrics_route_enabled')) {
            return;
        }

        /** @var Router $router */
        $router = $this->app->get('router');

        // override route so we can add the audit middleware
        $path = config('prometheus.metrics_route_path');
        assert(is_string($path));

        $router->withoutMiddleware('audit.requests')
            ->get($path, MetricsController::class . '@getMetrics')
            ->name('metrics');
    }

    public function register(): void
    {
        $this->app->bind(MetricRepository::class, PrometheusRepository::class);

        // override exporter so we can disable the default metrics
        $this->app->singleton(PrometheusExporter::class, function ($app) {
            $adapter = $app['prometheus.storage_adapter'];
            $prometheus = new CollectorRegistry($adapter, false);

            $namespace = config('prometheus.namespace');
            assert(is_string($namespace));

            $collectorClasses = config('prometheus.collectors');
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

        // override storage adapter so we can use Predis
        $this->app->bind(Adapter::class, static function () {
            $client = RedisManager::connection('prometheus')->client();
            assert($client instanceof Client || $client instanceof \Redis);
            return Redis::forConnection($client);
        });
    }
}
