<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Eloquent\EloquentCase;
use App\Repositories\CaseRepository;
use MinVWS\DBCO\Enum\Models\BCOStatus;

class CaseArchiveService
{
    public function __construct(
        private readonly int $archiveStaleCompletedCasesInDays,
        private readonly CaseRepository $caseRepository,
        private readonly PolicyVersionService $policyVersionService,
    ) {
    }

    public function archiveCase(EloquentCase $case): void
    {
        if ($case->bcoStatus !== BCOStatus::completed()) {
            $policyVersionUuid = $this->policyVersionService->getActivePolicyVersion()->uuid;
        }

        $this->caseRepository->archive($case, $policyVersionUuid ?? null);
    }

    public function archiveStaleCompleted(): int
    {
        $staleCases = $this->caseRepository->getStaleCasesByBCOStatus(
            $this->archiveStaleCompletedCasesInDays,
            BCOStatus::completed(),
        );

        foreach ($staleCases as $staleCase) {
            $this->archiveCase($staleCase);
        }

        return $staleCases->count();
    }
}
