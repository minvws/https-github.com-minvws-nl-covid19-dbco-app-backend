<?php

declare(strict_types=1);

namespace App\Services\Osiris;

use App\Jobs\ExportCaseToOsiris;
use App\Models\Enums\Osiris\CaseExportType;
use App\Repositories\CaseOsirisNotificationRepository;

final readonly class CaseExportRetryService
{
    public function __construct(
        private CaseOsirisNotificationRepository $caseOsirisNotificationRepository,
    ) {
    }

    public function exportOverdueCases(): void
    {
        foreach ($this->caseOsirisNotificationRepository->getUpdatedCasesWithoutRecentOsirisNotification() as $case) {
            if (NotificationService::isOsirisNotificationMissingForCase($case)) {
                ExportCaseToOsiris::dispatchIfEnabled($case->uuid, CaseExportType::INITIAL_ANSWERS);

                continue;
            }

            if (NotificationService::isOsirisFinalNotificationNeededForCase($case)) {
                ExportCaseToOsiris::dispatchIfEnabled($case->uuid, CaseExportType::DEFINITIVE_ANSWERS);
            }
        }

        foreach ($this->caseOsirisNotificationRepository->findRetryableDeletedCases() as $case) {
            if ($case->osirisNotifications()->count() === 0) {
                continue;
            }

            $notification = $this->caseOsirisNotificationRepository->findLatestDeletedStatusNotification($case);
            if ($notification?->notified_at->isAfter($case->deletedAt)) {
                continue;
            }

            ExportCaseToOsiris::dispatchIfEnabled($case->uuid, CaseExportType::DELETED_STATUS);
        }
    }
}
