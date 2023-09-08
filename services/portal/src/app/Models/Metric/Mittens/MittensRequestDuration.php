<?php

declare(strict_types=1);

namespace App\Models\Metric\Mittens;

use App\Events\Mittens\MittensRequestDurationMeasured;
use App\Models\Metric\HistogramMetric;
use MinVWS\Timer\Duration;

class MittensRequestDuration extends HistogramMetric
{
    protected string $name = 'mittens:request_duration_seconds';
    protected string $help = 'The duration of a request to mittens';

    /** @var array<float> */
    protected ?array $buckets = [
        0.005,
        0.01,
        0.025,
        0.05,
        0.075,
        0.1,
        0.25,
        0.5,
        0.75,
        1.0,
        2.5,
        5.0,
        7.5,
        10.0,
    ];

    private function __construct(
        string $uri,
        public Duration $duration,
    )
    {
        $this->labels = [
            'uri' => $uri,
        ];
        $this->value = $duration->inSeconds();
    }

    public static function measureFromEvent(MittensRequestDurationMeasured $event): MittensRequestDuration
    {
        return new self($event->uri, $event->duration);
    }
}
