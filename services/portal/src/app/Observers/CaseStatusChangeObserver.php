<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Eloquent\EloquentCase;
use App\Repositories\CaseStatusHistoryRepository;

class CaseStatusChangeObserver
{
    public function __construct(
        private readonly CaseStatusHistoryRepository $caseStatusHistoryRepository,
    ) {
    }

    public function saving(EloquentCase $case): void
    {
        if (!$case->exists || $case->isClean('bco_status')) {
            return;
        }

        $this->caseStatusHistoryRepository->create($case);
    }
}
