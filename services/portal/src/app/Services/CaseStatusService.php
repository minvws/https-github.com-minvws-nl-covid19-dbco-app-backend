<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CaseStatusRepository;
use DBCO\Shared\Application\Metrics\Events\ExpiredEvent;
use MinVWS\Metrics\Services\EventService;
use Psr\Log\LoggerInterface;

class CaseStatusService
{
    public function __construct(
        private readonly CaseStatusRepository $caseStatusRepository,
        private readonly EventService $eventService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Updates the bco_status and index_status fields of a covid case
     * for time dependant statuses. The following statuses are transitioned:
     *  - CovidCase::INDEX_STATUS_TIMEOUT
     *  - CovidCase::INDEX_STATUS_EXPIRED
     */
    public function updateAllTimeSensitiveStatus(int $limit): void
    {
        $this->updateTimeoutIndexStatus($limit);
        $this->updateExpiredIndexStatus($limit);
    }

    private function updateTimeoutIndexStatus(int $limit): void
    {
        $numberOfRowsAffected = $this->caseStatusRepository->updateTimeoutIndexStatus($limit);

        if ($numberOfRowsAffected === $limit) {
            $this->logger->warning('Number of affected rows is equal to limit for updateTimeoutIndexStatus');
        }
    }

    private function updateExpiredIndexStatus(int $limit): void
    {
        $expiredCaseUuids = $this->caseStatusRepository->updateExpiredIndexStatus($limit);
        foreach ($expiredCaseUuids as $expiredCaseUuid) {
            $this->eventService->registerEvent(new ExpiredEvent(ExpiredEvent::ACTOR_INDEX, $expiredCaseUuid));
        }

        if ($expiredCaseUuids->count() === $limit) {
            $this->logger->warning('Number of affected rows is equal to limit for updateExpiredIndexStatus');
        }
    }
}
