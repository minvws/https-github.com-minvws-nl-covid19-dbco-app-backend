<?php

declare(strict_types=1);

namespace App\Listeners\Osiris;

use App\Events\Osiris\CaseExportRejected;
use App\Models\Metric\Osiris\CaseExportFailed as CaseExportFailedMetric;
use App\Models\Metric\Osiris\CaseExportNullCase;
use App\Models\Metric\Osiris\ValidationResponse;
use App\Services\MetricService;

final class MeasureOsirisExportFailure
{
    public function __construct(
        private readonly MetricService $metricService,
    ) {
    }

    public function whenCaseExportWasRejected(CaseExportRejected $event): void
    {
        $this->metricService->measure(
            CaseExportFailedMetric::rejected(
                !empty($event->errors) ? ValidationResponse::HasErrors : ValidationResponse::None,
            ),
        );
    }

    public function whenExportClientEncounteredError(): void
    {
        $this->metricService->measure(CaseExportFailedMetric::failed());
    }

    public function whenCaseForExportIsMissing(): void
    {
        $this->metricService->measure(new CaseExportNullCase());
    }
}
