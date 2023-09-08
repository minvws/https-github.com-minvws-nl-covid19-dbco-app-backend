<?php

declare(strict_types=1);

namespace App\Listeners\Mittens;

use App\Events\Mittens\MittensRequestDurationMeasured;
use App\Models\Metric\Mittens\MittensRequestDuration;
use App\Services\MetricService;

class MittensRequestDurationMeasuredHandler
{
    public function __construct(
        private readonly MetricService $metricService,
    )
    {
    }

    public function handle(
        MittensRequestDurationMeasured $event,
    ): void
    {
        $this->metricService->measure(MittensRequestDuration::measureFromEvent($event));
    }
}
