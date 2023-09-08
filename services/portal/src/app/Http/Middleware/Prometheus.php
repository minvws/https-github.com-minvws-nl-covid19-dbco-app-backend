<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Helpers\Config;
use App\Models\Metric\Http\Response as ResponseMetric;
use App\Models\Metric\Http\ResponseTime;
use App\Models\Metric\Http\ResponseTimePerOrganisation;
use App\Services\AuthenticationService;
use App\Services\MetricService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use MinVWS\Timer\Duration;
use MinVWS\Timer\Timer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * We use our own Prometheus route middleware for more control.
 */
class Prometheus
{
    public function __construct(
        private readonly AuthenticationService $authService,
        private readonly MetricService $metricService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $timer = Timer::start();

        /** @var Response $response */
        $response = $next($request);

        $duration = $timer->stop();

        try {
            $route = $this->getMatchedRoute($request)->uri();
        } catch (NotFoundHttpException | MethodNotAllowedHttpException) {
            $route = $request->path();
        }

        $this->handleRouteCounter($request, $route, $response);
        $this->handleRouteHistogram($request, $route, $duration);
        $this->handleRouteHistogramPerOrganisation($duration);

        return $response;
    }

    private function getMatchedRoute(Request $request): Route
    {
        return RouteFacade::getRoutes()->match($request);
    }

    private function handleRouteCounter(Request $request, string $routeOrUri, Response $response): void
    {
        $this->metricService->measure(
            new ResponseMetric($request->method(), $routeOrUri, $response->getStatusCode()),
        );
    }

    private function handleRouteHistogram(Request $request, string $routeOrUri, Duration $duration): void
    {
        $this->metricService->measure(new ResponseTime(
            $request->method(),
            $routeOrUri,
            $duration->inSeconds(),
            Config::arrayOrNull('prometheus.routes_buckets'),
        ));
    }

    private function handleRouteHistogramPerOrganisation(Duration $duration): void
    {
        $organisation = $this->authService->getSelectedOrganisation();
        if ($organisation === null) {
            return;
        }

        $this->metricService->measure(new ResponseTimePerOrganisation(
            $organisation->name,
            $duration->inSeconds(),
            Config::arrayOrNull('prometheus.routes_buckets'),
        ));
    }
}
