<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Jobs\ExportCaseToOsiris;
use App\Jobs\RateLimited\RateLimitable;
use App\Models\Metric\Job\JobResult as JobResultMetric;
use App\Services\MetricService;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;

final class JobResultSubscriber
{
    public function __construct(
        private readonly MetricService $metricService,
    ) {
    }

    public function handleProcessed(JobProcessed $event): void
    {
        if (!$this->shouldHandle($event->job)) {
            return;
        }

        $fullyQualifiedClassName = $event->job->resolveName();

        $this->metricService->measure(JobResultMetric::success($fullyQualifiedClassName, $event->connectionName));
    }

    public function handleFailed(JobFailed $event): void
    {
        if (!$this->shouldHandle($event->job)) {
            return;
        }

        $fullyQualifiedClassName = $event->job->resolveName();

        $this->metricService->measure(JobResultMetric::failed($fullyQualifiedClassName, $event->connectionName));
    }

    private function shouldHandle(Job $job): bool
    {
        $fullyQualifiedClassName = $job->resolveName();

        // Osiris deviates from the default laravel queue handling
        if ($fullyQualifiedClassName === ExportCaseToOsiris::class) {
            return false;
        }

        // When the the RateLimiter is active we don't register job metrics
        return !($job instanceof RateLimitable && $job->isPostponed());
    }

    public function subscribe(): array
    {
        return [
            JobProcessed::class => 'handleProcessed',
            JobFailed::class => 'handleFailed',
        ];
    }
}
