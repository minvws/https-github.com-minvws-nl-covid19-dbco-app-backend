<?php

declare(strict_types=1);

namespace App\Models\Metric\RateLimiter;

use App\Models\Metric\GaugeMetric;

final class RateLimiterHit extends GaugeMetric
{
    protected string $name = 'rate_limiter:hit';
    protected string $help = 'Represents the rate limiter hit count';

    public function __construct(float $value, string $limiterName)
    {
        $this->value = $value;
        $this->labels = [
            'context' => $limiterName,
        ];
    }
}
