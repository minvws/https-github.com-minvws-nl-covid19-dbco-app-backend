<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\CircuitBreaker\CircuitBreaker;
use App\Models\Metric\CircuitBreaker\Availability;

final class CircuitBreakerService
{
    public function __construct(
        private readonly CircuitBreaker $circuitBreaker,
        private readonly MetricService $metricService,
    ) {
    }

    public function isAvailable(string $service): bool
    {
        return $this->circuitBreaker->isAvailable($service);
    }

    public function registerFailure(string $service): void
    {
        $this->circuitBreaker->registerFailure($service);
    }

    public function registerSuccess(string $service): void
    {
        $this->circuitBreaker->registerSuccess($service);
    }

    public function measureAvailability(string $service, bool $isAvailable): void
    {
        if ($isAvailable) {
            $this->metricService->measure(Availability::available($service));
        } else {
            $this->metricService->measure(Availability::notAvailable($service));
        }
    }
}
