<?php

declare(strict_types=1);

namespace App\Listeners\Osiris;

use App\Events\JobHandled;
use App\Jobs\ExportCaseToOsiris;
use App\Models\Metric\Osiris\CaseExportJobAttempts;
use App\Models\Metric\Osiris\CaseExportJobRun;
use App\Models\Metric\Osiris\CaseTestresultBoundToForwardedDuration;
use App\Services\MetricService;
use Illuminate\Contracts\Queue\Job;

final class JobHandledListener
{
    public function __construct(
        private readonly MetricService $metricService,
    ) {
    }

    public function handle(JobHandled $event): void
    {
        $job = $event->job;

        if ($job->resolveName() !== ExportCaseToOsiris::class) {
            return;
        }

        $this->metricService->measure(new CaseTestresultBoundToForwardedDuration($event->duration));

        $this->handleJob($job);
    }

    private function handleJob(Job $job): void
    {
        $isSucceeded = !$job->hasFailed() && !$job->isReleased();
        $maxTries = $job->maxTries();
        $attempts = $job->attempts();

        if ($isSucceeded) {
            $this->measureSuccess($maxTries, $attempts);
        } else {
            $this->measureFailure($maxTries, $attempts);
        }
    }

    private function measureSuccess(?int $maxTries, int $attempts): void
    {
        $this->metricService->measure(CaseExportJobRun::success());

        if ($maxTries > 0) {
            $this->metricService->measure(CaseExportJobAttempts::success($attempts, $maxTries));
        }
    }

    private function measureFailure(?int $maxTries, int $attempts): void
    {
        $this->metricService->measure(CaseExportJobRun::failed());

        if ($maxTries > 0) {
            $this->metricService->measure(CaseExportJobAttempts::failed($attempts, $maxTries));
        }
    }
}
