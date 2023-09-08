<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Metric\Job\JobQueued as JobQueuedMetric;
use App\Services\MetricService;
use Illuminate\Queue\Events\JobQueued;

final readonly class JobQueuedListener
{
    public function __construct(
        private MetricService $metricService,
    ) {
    }

    public function handle(JobQueued $event): void
    {
        $this->metricService->measure(new JobQueuedMetric($event));
    }
}
