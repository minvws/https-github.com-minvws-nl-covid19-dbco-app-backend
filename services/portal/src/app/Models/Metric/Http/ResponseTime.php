<?php

declare(strict_types=1);

namespace App\Models\Metric\Http;

use App\Models\Metric\HistogramMetric;

final class ResponseTime extends HistogramMetric
{
    protected string $name = 'response_time_seconds';
    protected string $help = 'Observes response times';

    public function __construct(string $method, string $uri, float $duration, ?array $buckets)
    {
        $this->buckets = $buckets;
        $this->labels = [
            'method' => $method,
            'uri' => $uri,
        ];
        $this->value = $duration;
    }
}
