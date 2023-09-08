<?php

declare(strict_types=1);

namespace App\Services\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use App\Exceptions\SkippableTestResultReportImportException;
use App\Jobs\ImportTestResultReport;
use App\Models\Metric\TestResult\FailureReason;
use App\Services\MetricService;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;
use Throwable;

use function config;

final class TestResultReportImportHandler
{
    public function __construct(
        private readonly EncryptionService $encryptionService,
        private readonly TestResultReportImportServiceInterface $testResultReportImportService,
        private readonly MetricService $metricService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(ImportTestResultReport $job): void
    {
        try {
            $this->logger->info('Processing job...', ['job' => $job::class, 'messageId' => $job->getMessageId()]);
            $testResultReport = TestResultReport::fromArray($job->getPayload($this->encryptionService));
            $this->logger->info('Extracted test result report from job', [
                'messageId' => $job->getMessageId(),
                'receivedAt' => $testResultReport->receivedAt,
            ]);

            DB::transaction(
                function (ConnectionInterface $connection) use ($testResultReport): void {
                    $this->logger->info(
                        'Importing test result report...',
                        ['messageId' => $testResultReport->messageId],
                    );
                    $this->testResultReportImportService->import($testResultReport);

                    if (!config('misc.test_result.simulation_mode_enabled')) {
                        return;
                    }

                    $this->logger->notice(
                        'Simulation mode enabled; performing rollback...',
                        ['messageId' => $testResultReport->messageId],
                    );
                    $connection->rollBack();
                },
            );

            $this->logger->info('Finished import of test result report', ['messageId' => $job->getMessageId()]);
        } catch (SkippableTestResultReportImportException $skippableTestResultReportImportException) {
            $this->logger->info(
                'Skipped import of test result report',
                [
                    'messageId' => $job->getMessageId(),
                    'reason' => $skippableTestResultReportImportException->getMessage(),
                ],
            );
        } catch (Throwable $throwable) {
            $this->metricService->measure(FailureReason::fromThrowable($throwable));

            $this->logger->error(
                'Caught unexpected throwable while processing job',
                ['messageId' => $job->getMessageId(), 'trace' => $throwable],
            );

            throw $throwable;
        }
    }
}
