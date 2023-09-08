<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\CaseStatusHistory;
use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\BCOStatus;

class DbCaseStatusHistoryRepository implements CaseStatusHistoryRepository
{
    public function create(EloquentCase $case): CaseStatusHistory
    {
        $history = new CaseStatusHistory();
        $history->case()->associate($case);
        $history->bco_status = $case->bco_status;
        $history->save();

        return $history;
    }

    public function getByStatus(EloquentCase $case, BCOStatus $BCOStatus): ?CaseStatusHistory
    {
        return $case->statusHistory()->orderByDesc('changed_at')->where('bco_status', $BCOStatus)->first();
    }
}
