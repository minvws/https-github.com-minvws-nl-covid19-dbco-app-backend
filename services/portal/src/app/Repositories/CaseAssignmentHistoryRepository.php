<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\CaseAssignmentHistory;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use Illuminate\Support\Collection;

interface CaseAssignmentHistoryRepository
{
    public function registerCaseAssignment(EloquentCase $case, EloquentUser $assigner): CaseAssignmentHistory;

    /**
     * @param array<string> $covidCaseUuids
     *
     * @return Collection<int,CaseAssignmentHistory>
     */
    public function findByCaseUuidAssignedSince(array $covidCaseUuids, string $since): Collection;
}
