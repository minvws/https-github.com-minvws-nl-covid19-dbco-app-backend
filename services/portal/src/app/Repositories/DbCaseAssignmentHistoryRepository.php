<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\CaseAssignmentHistory;
use App\Models\Eloquent\CaseList;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class DbCaseAssignmentHistoryRepository implements CaseAssignmentHistoryRepository
{
    private static array $caseListCache = [];

    public function registerCaseAssignment(EloquentCase $case, EloquentUser $assigner): CaseAssignmentHistory
    {
        $assignedCaseListName = $case->assigned_case_list_uuid !== null
            ? $this->getListName($case->assigned_case_list_uuid)
            : null;

        return CaseAssignmentHistory::create([
            'covidcase_uuid' => $case->uuid,
            'assigned_user_uuid' => $case->assigned_user_uuid,
            'assigned_organisation_uuid' => $case->assigned_organisation_uuid,
            'assigned_case_list_uuid' => $case->assigned_case_list_uuid,
            'assigned_case_list_name' => $assignedCaseListName,
            'assigned_at' => CarbonImmutable::now(),
            'assigned_by' => $assigner->uuid,
        ]);
    }

    private function getListName(string $caseListUuid): string
    {
        if (!isset(self::$caseListCache[$caseListUuid])) {
            /** @var CaseList|null $caseList */
            $caseList = CaseList::withoutGlobalScopes()->find($caseListUuid, ['name']);
            if ($caseList !== null) {
                self::$caseListCache[$caseListUuid] = $caseList->name;
            } else {
                self::$caseListCache[$caseListUuid] = '';
                Log::error('Case assigned to unknown caseList: ' . $caseListUuid);
            }
        }

        return self::$caseListCache[$caseListUuid];
    }

    /**
     * @return Collection<int,CaseAssignmentHistory>
     */
    public function findByCaseUuidAssignedSince(array $covidCaseUuids, string $since): Collection
    {
        return CaseAssignmentHistory::query()
            ->whereIn('covidcase_uuid', $covidCaseUuids)
            ->where('assigned_at', '>', $since)
            ->orderBy('covidcase_uuid', 'desc')
            ->orderBy('assigned_at', 'desc')
            ->get();
    }
}
