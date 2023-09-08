<?php

declare(strict_types=1);

namespace App\Listeners\Osiris;

use App\Events\Osiris\CaseExportFailed;
use App\Events\Osiris\CaseExportRejected;
use App\Events\Osiris\CaseValidationRaisesWarning;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\OsirisHistory;
use App\Repositories\CaseLabelRepository;
use App\Repositories\CaseRepository;

class ReopenCase
{
    public function __construct(
        private readonly CaseRepository $caseRepository,
        private readonly CaseLabelRepository $caseLabelRepository,
    ) {
    }

    public function whenCaseExportWasRejected(CaseExportRejected $event): void
    {
        $this->reopenCase($event->case);
        $this->addLabel($event->case);
    }

    public function whenExportClientEncounteredError(CaseExportFailed $event): void
    {
        $this->reopenCase($event->case);
        $this->addLabel($event->case);
    }

    public function whenCaseValidationRaisesWarning(CaseValidationRaisesWarning $event): void
    {
        $this->reopenCase($event->case);
    }

    private function reopenCase(EloquentCase $case): void
    {
        if (!$case->isReopenable()) {
            return;
        }

        $this->caseRepository->reopenCase($case);
    }

    private function addLabel(EloquentCase $case): void
    {
        $this->caseRepository->addCaseLabel(
            $case,
            $this->caseLabelRepository->getLabelByCode(OsirisHistory::OSIRIS_NOTIFICATION_FAILED_LABEL),
        );
    }
}
