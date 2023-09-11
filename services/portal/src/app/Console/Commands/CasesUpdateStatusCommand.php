<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CaseStatusService;
use Illuminate\Console\Command;
use Psr\Log\LoggerInterface;
use Throwable;

use function sprintf;

/**
 * Periodic command which updates the bco_status and index_status fields of a covid case
 * for time dependant statuses. The following statuses are transitioned by this
 * command:
 *  - CovidCase::INDEX_STATUS_TIMEOUT
 *  - CovidCase::INDEX_STATUS_EXPIRED
 */
class CasesUpdateStatusCommand extends Command
{
    /** @var string $signature */
    protected $signature = 'cases:update-status
        {--limit=1000 : The max amount of cases to update per query}
    ';

    /** @var string $description */
    protected $description = 'Update time dependant status of covid cases.';

    public function handle(
        CaseStatusService $caseStatusService,
        LoggerInterface $logger,
    ): int {
        $limit = (int) $this->option('limit');

        try {
            $caseStatusService->updateAllTimeSensitiveStatus($limit);
        } catch (Throwable $exception) {
            $logger->warning(
                sprintf('Updating time dependant status of covid cases failed: %s', $exception->getMessage()),
            );

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
