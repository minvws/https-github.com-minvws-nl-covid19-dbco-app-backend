<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Helpers\FeatureFlagHelper;
use App\Services\CaseMetrics\CaseMetricsService;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class CaseMetricsRefreshJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public int $tries = 1;

    public function __construct(
        public readonly string $organisationUuid,
        public readonly CarbonInterface $periodEnd,
    ) {
    }

    public function handle(
        CaseMetricsService $caseMetricsService,
        LoggerInterface $logger,
    ): void {
        if (FeatureFlagHelper::isDisabled('case_metrics_enabled')) {
            return;
        }

        try {
            $caseMetricsService->refreshForOrganisation($this->organisationUuid, $this->periodEnd);
        } catch (Throwable $throwable) {
            $logger->error('CaseMetricsRefreshJob failed', ['exception' => $throwable]);

            $this->fail($throwable);
        }
    }
}
