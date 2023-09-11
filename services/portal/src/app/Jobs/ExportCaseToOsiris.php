<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\JobHandled;
use App\Exceptions\Osiris\CaseExport\CaseExportExceptionInterface;
use App\Exceptions\Osiris\CaseExport\CaseExportRejectedException;
use App\Helpers\Config;
use App\Helpers\FeatureFlagHelper;
use App\Jobs\Middleware\RateLimited;
use App\Models\Enums\Osiris\CaseExportType;
use App\Services\Osiris\OsirisCaseExportStrategy;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use Psr\Log\LoggerInterface;
use Throwable;

use function sprintf;

/**
 * @method static PendingDispatch dispatch(string $caseUuid, CaseExportType $caseExportType)
 * @method static mixed dispatchSync(string $caseUuid, CaseExportType $caseExportType)
 */
final class ExportCaseToOsiris implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;
    use Queueable;

    public readonly int $backoff;
    public readonly int $tries;
    public readonly int $timeout;
    public readonly DateTimeInterface $startTime;

    public function __construct(
        public readonly string $caseUuid,
        public readonly CaseExportType $caseExportType,
    ) {
        $this->backoff = Config::integer('services.osiris.case_export_job.backoff');
        $this->tries = Config::integer('services.osiris.case_export_job.tries');
        $this->timeout = Config::integer('services.osiris.case_export_job.timeout');
        $this->startTime = CarbonImmutable::now();
    }

    public static function dispatchIfEnabled(string $caseUuid, CaseExportType $caseExportType): PendingDispatch|Fluent
    {
        $shouldDispatch = FeatureFlagHelper::isEnabled('osiris_send_case_enabled');

        Log::debug(
            sprintf('Dispatch %s job to queue if enabled - %s', self::class, $shouldDispatch ? 'ENABLED' : 'DISABLED'),
            [
                'caseUuid' => $caseUuid,
                'caseExportType' => $caseExportType->value,
            ],
        );

        if (!$shouldDispatch) {
            return new Fluent();
        }

        return self::dispatch($caseUuid, $caseExportType)
            ->onQueue(Config::string('services.osiris.case_export_job.queue_name'))
            ->onConnection(Config::string('services.osiris.case_export_job.connection'));
    }

    /**
     * @throws Throwable
     */
    #[SetAuditEventDescription('Send case notification to Osiris RIVM')]
    public function handle(LoggerInterface $logger, OsirisCaseExportStrategy $strategy): void
    {
        $this->logStart($logger);

        try {
            $strategy->execute($this->caseUuid);
        } catch (CaseExportRejectedException $exception) {
            $this->delete();
            $this->logFailure($logger, $exception);
        } catch (Throwable $exception) {
            $this->attempts() < $this->tries ? $this->release($this->backoff) : $this->delete();
            $this->logFailure($logger, $exception);
        } finally {
            JobHandled::dispatch($this->job, CarbonImmutable::now()->floatDiffInSeconds($this->startTime));
        }
    }

    /**
     * @return array<int,object>
     */
    public function middleware(): array
    {
        return [
            new RateLimited(Config::string('services.osiris.rate_limit.rate_limiter_key')),
        ];
    }

    private function logStart(LoggerInterface $logger): void
    {
        $logger->info(sprintf('Processing job "%s"...', self::class), [
            'caseUuid' => $this->caseUuid,
            'caseExportType' => $this->caseExportType,
            'startTime' => $this->startTime,
        ]);
    }

    private function logFailure(LoggerInterface $logger, Throwable $exception): void
    {
        $attempts = $this->attempts();
        $context = [
            'error' => $exception->getMessage(),
            'attempt' => $attempts,
            'lastAttempt' => $attempts >= $this->tries ? 'true' : 'false',
        ];
        if (!$exception instanceof CaseExportExceptionInterface) {
            $context['trace'] = $exception;
        }

        $logger->error(sprintf('Failed to process job "%s"', self::class), $context);
    }
}
