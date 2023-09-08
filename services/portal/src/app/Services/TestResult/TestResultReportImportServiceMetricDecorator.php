<?php

declare(strict_types=1);

namespace App\Services\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use App\Exceptions\SkippableTestResultReportImportException;
use App\Exceptions\TestReportingNotAllowedForOrganisationException;
use App\Models\Metric\TestResult\ImportDuration;
use App\Models\Metric\TestResult\ImportStatus;
use App\Models\Metric\TestResult\ProcessingDuration;
use App\Models\Metric\TestResult\ReportingNotAllowedForOrganisation;
use App\Services\MetricService;
use MinVWS\Timer\Timer;
use Throwable;

final class TestResultReportImportServiceMetricDecorator implements TestResultReportImportServiceInterface
{
    public function __construct(
        private readonly TestResultReportImportService $decorated,
        private readonly ProcessingDurationCalculator $processingDurationCalculator,
        private readonly MetricService $metricService,
    ) {
    }

    /**
     * @throws SkippableTestResultReportImportException
     * @throws Throwable
     */
    public function import(TestResultReport $testResultReport): void
    {
        try {
            $importDuration = Timer::wrap(
                function () use ($testResultReport): void {
                    $this->decorated->import($testResultReport);
                },
            );

            $this->metricService->measure(new ImportDuration($importDuration->inSeconds()));
            $this->metricService->measure(ImportStatus::processed());
            $this->metricService->measure(new ProcessingDuration(
                $this->processingDurationCalculator->diffInSecondsSinceReceived($testResultReport),
            ));
        } catch (TestReportingNotAllowedForOrganisationException $testReportingNotAllowedForOrganisationException) {
            $this->metricService->measure(new ReportingNotAllowedForOrganisation($testResultReport->ggdIdentifier));
            $this->metricService->measure(ImportStatus::processed());
            throw $testReportingNotAllowedForOrganisationException;
        } catch (SkippableTestResultReportImportException $skippableTestResultReportImportException) {
            $this->metricService->measure(ImportStatus::processed());
            throw $skippableTestResultReportImportException;
        } catch (Throwable $throwable) {
            $this->metricService->measure(ImportStatus::failed());
            throw $throwable;
        }
    }
}
