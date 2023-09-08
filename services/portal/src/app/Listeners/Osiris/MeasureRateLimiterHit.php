<?php

declare(strict_types=1);

namespace App\Listeners\Osiris;

use App\Events\RateLimiter\RateLimiterHit as RateLimiterHitEvent;
use App\Models\Metric\RateLimiter\RateLimiterHit as RateLimiterHitMetric;
use App\Services\MetricService;

final class MeasureRateLimiterHit
{
    public function __construct(
        private readonly MetricService $metricService,
    ) {
    }

    public function handle(RateLimiterHitEvent $event): void
    {
        $this->metricService->measure(
            new RateLimiterHitMetric((float) $event->hitCount, $event->limiterName),
        );
    }
}
