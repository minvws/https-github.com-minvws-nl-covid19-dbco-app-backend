<?php

declare(strict_types=1);

namespace App\Listeners\Osiris;

use App\Dto\OsirisHistory\OsirisHistoryDto;
use App\Dto\OsirisHistory\OsirisHistoryValidationResponse;
use App\Events\Osiris\CaseExportFailed;
use App\Events\Osiris\CaseExportRejected;
use App\Events\Osiris\CaseExportSucceeded;
use App\Events\Osiris\CaseNotExportable;
use App\Events\Osiris\CaseValidationRaisesWarning;
use App\Repositories\HistoryRepository;
use App\Services\Osiris\SoapMessage\SoapMessageBuilder;
use MinVWS\DBCO\Enum\Models\OsirisHistoryStatus;

class CreateOsirisHistory
{
    public function __construct(
        private readonly HistoryRepository $historyRepository,
    ) {
    }

    public function whenCaseNotExportable(CaseNotExportable $event): void
    {
        $this->historyRepository->addToOsirisHistory(
            new OsirisHistoryDto(
                $event->case->uuid,
                OsirisHistoryStatus::blocked(),
                SoapMessageBuilder::mapToStatus($event->caseExportType),
                null,
            ),
        );
    }

    public function whenCaseValidationRaisesWarning(CaseValidationRaisesWarning $event): void
    {
        $this->historyRepository->addToOsirisHistory(
            new OsirisHistoryDto(
                $event->case->uuid,
                OsirisHistoryStatus::validation(),
                SoapMessageBuilder::mapToStatus($event->caseExportType),
                OsirisHistoryValidationResponse::fromValidationResult($event->validationResult),
            ),
        );
    }

    public function whenCaseExportSucceeded(CaseExportSucceeded $event): void
    {
        $this->historyRepository->addToOsirisHistory(
            new OsirisHistoryDto(
                $event->case->uuid,
                OsirisHistoryStatus::success(),
                SoapMessageBuilder::mapToStatus($event->caseExportType),
                new OsirisHistoryValidationResponse(warnings: $event->caseExportResult->warnings),
            ),
        );
    }

    public function whenCaseExportWasRejected(CaseExportRejected $event): void
    {
        $this->historyRepository->addToOsirisHistory(
            new OsirisHistoryDto(
                $event->case->uuid,
                OsirisHistoryStatus::failed(),
                SoapMessageBuilder::mapToStatus($event->caseExportType),
                new OsirisHistoryValidationResponse($event->errors),
            ),
        );
    }

    public function whenExportClientEncounteredError(CaseExportFailed $event): void
    {
        $this->historyRepository->addToOsirisHistory(
            new OsirisHistoryDto(
                $event->case->uuid,
                OsirisHistoryStatus::failed(),
                SoapMessageBuilder::mapToStatus($event->caseExportType),
                null,
            ),
        );
    }
}
