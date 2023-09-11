<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Arquivei\LaravelPrometheusExporter\PrometheusExporter;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use MinVWS\Timer\Duration;
use MinVWS\Timer\Timer;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

use function array_keys;
use function array_values;
use function assert;
use function config;
use function is_array;

final class PrometheusRouteMiddleware
{
    private PrometheusExporter $prometheus;
    private LoggerInterface $logger;

    public function __construct(PrometheusExporter $prometheus, LoggerInterface $logger)
    {
        $this->prometheus = $prometheus;
        $this->logger = $logger;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $timer = Timer::start();

        /** @var Response $response */
        $response = $next($request);

        $duration = $timer->stop();

        try {
            $route = $this->getMatchedRoute($request);
            $this->handleRouteCounter($request, $route, $response);
            $this->handleRouteHistogram($request, $route, $duration);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $this->logger->error($e->getTraceAsString());
        }

        return $response;
    }

    private function handleRouteCounter(Request $request, Route $route, Response $response): void
    {
        $labels = [
            'method' => $request->method(),
            'uri' => $route->uri(),
            'status' => (string) $response->getStatusCode(),
        ];

        // increment
        $counter = $this->prometheus->getOrRegisterCounter(
            'response_status_counter',
            'Counts the response status codes',
            array_keys($labels),
        );
        $counter->inc(array_values($labels));
    }

    private function handleRouteHistogram(Request $request, Route $route, Duration $duration): void
    {
        $labels = ['method' => $request->method(), 'uri' => $route->uri()];

        $buckets = config('prometheus.routes_buckets') ?? null;
        assert($buckets === null || is_array($buckets));

        $histogram = $this->prometheus->getOrRegisterHistogram(
            'response_time_seconds',
            'Observes response times',
            array_keys($labels),
            $buckets,
        );

        $histogram->observe($duration->inSeconds(), array_values($labels));
    }

    private function getMatchedRoute(Request $request): Route
    {
        $routeCollection = RouteFacade::getRoutes();

        return $routeCollection->match($request);
    }
}
