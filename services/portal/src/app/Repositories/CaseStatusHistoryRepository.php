<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\CaseStatusHistory;
use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\BCOStatus;

interface CaseStatusHistoryRepository
{
    public function create(EloquentCase $case): CaseStatusHistory;

    public function getByStatus(EloquentCase $case, BCOStatus $BCOStatus): ?CaseStatusHistory;
}
