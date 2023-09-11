<?php

declare(strict_types=1);

namespace App\Listeners\Osiris;

use App\Events\Osiris\CaseExportSucceeded;
use App\Models\Enums\Osiris\CaseExportType;
use App\Models\Metric\Osiris\CaseExportSucceeded as CaseExportSucceededMetric;
use App\Services\MetricService;
use Psr\Log\LoggerInterface;

final class LogOsirisExportSuccess
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly MetricService $metricService,
    ) {
    }

    public function __invoke(CaseExportSucceeded $event): void
    {
        $this->metricService->measure($this->getMetric($event));

        $this->logger->info(
            'Successfully exported case to Osiris',
            [
                'caseUuid' => $event->caseExportResult->caseUuid,
                'reportNumber' => $event->caseExportResult->reportNumber,
                'osirisNumber' => $event->caseExportResult->osirisNumber->toInt(),
                'questionnaireVersion' => $event->caseExportResult->questionnaireVersion,
                'warnings' => $event->caseExportResult->warnings,
            ],
        );
    }

    private function getMetric(CaseExportSucceeded $event): CaseExportSucceededMetric
    {
        $metric = empty($event->caseExportResult->warnings)
            ? CaseExportSucceededMetric::withoutWarnings()
            : CaseExportSucceededMetric::withWarnings();

        if ($event->caseExportType === CaseExportType::INITIAL_ANSWERS) {
            $metric->initial();
        }

        return $metric;
    }
}
