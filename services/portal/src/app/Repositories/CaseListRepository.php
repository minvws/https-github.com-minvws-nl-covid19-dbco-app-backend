<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\RequiredOrganisationNotFoundException;
use App\Models\CaseList\ListOptions;
use App\Models\Eloquent\CaseList;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;

use function collect;
use function in_array;
use function sprintf;

class CaseListRepository
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function listCaseLists(ListOptions $options): LengthAwarePaginator
    {
        $query = CaseList::query()
            ->orderByDesc('is_default')
            ->orderBy('name');

        if ($options->onlyQueues()) {
            $query->where('is_queue', 1);
        } elseif ($options->onlyLists()) {
            $query->where('is_queue', 0);
        }

        $paginator = $query->paginate($options->perPage, ['*'], '', $options->page);

        if ($options->stats && $paginator->isNotEmpty()) {
            $this->addStatsToCaseListCollection($paginator->getCollection());
        }

        return $paginator;
    }

    public function getDefaultCaseQueue(bool $withStats = false): ?CaseList
    {
        try {
            /** @var CaseList $caseList */
            $caseList = CaseList::query()
                ->where('is_default', 1)
                ->where('is_queue', 1)
                ->firstOrFail();
        } catch (RequiredOrganisationNotFoundException) {
            $this->logger->info(sprintf('%s failed (requiredSelectedOrganisation not found)', __METHOD__));

            return null;
        }

        return $withStats ? $this->addStatsToCaseList($caseList) : $caseList;
    }

    public function getCaseListByUuid(string $uuid, bool $withStats): ?CaseList
    {
        $caseList = CaseList::query()->find($uuid);

        if (!$caseList instanceof CaseList) {
            return null;
        }

        return $withStats ? $this->addStatsToCaseList($caseList) : $caseList;
    }

    public function createCaseList(CaseList $caseList): bool
    {
        return $caseList->save();
    }

    public function updateCaseList(CaseList $caseList): bool
    {
        return $caseList->save();
    }

    public function deleteCaseList(CaseList $caseList): bool
    {
        return (bool) $caseList->delete();
    }

    private function assignStatsToCaseList(CaseList $caseList, array $stats): void
    {
        $caseList->assignedCasesCount = $stats[$caseList->uuid]['assigned_cases_count'] ?? 0;
        $caseList->unassignedCasesCount = $stats[$caseList->uuid]['unassigned_cases_count'] ?? 0;
        $caseList->completedCasesCount = $stats[$caseList->uuid]['completed_cases_count'] ?? 0;
        $caseList->archivedCasesCount = $stats[$caseList->uuid]['archived_cases_count'] ?? 0;
    }

    /**
     * @param Collection<int,CaseList> $caseListCollection
     */
    private function addStatsToCaseListCollection(Collection $caseListCollection): void
    {
        $listUuids = $caseListCollection->pluck('uuid');
        $stats = $this->getStats($listUuids);
        $caseListCollection->transform(function (CaseList $list) use ($stats) {
            $this->assignStatsToCaseList($list, $stats);
            return $list;
        });
    }

    private function addStatsToCaseList(CaseList $caseList): CaseList
    {
        $stats = $this->getStats(collect([$caseList->uuid]));
        $this->assignStatsToCaseList($caseList, $stats);

        return $caseList;
    }

    private function getStats(Collection $listUuids): array
    {
        $rawStats = $this->getRawStats($listUuids);
        return $this->transformRawStats($listUuids, $rawStats);
    }

    /**
     * @param Collection<int, string> $listUuids
     *
     * @return Collection<object>
     */
    private function getRawStats(Collection $listUuids): Collection
    {
        return
            DB::table('covidcase')
            ->select([
                'assigned_case_list_uuid',
                'case_list_planner_view',
                DB::raw('COUNT(uuid) AS count'),
            ])
            ->whereIn('assigned_case_list_uuid', $listUuids)
            ->groupBy([
                'assigned_case_list_uuid',
                'case_list_planner_view',
            ])
            ->get();
    }

    /**
     * @param Collection<int, string> $listUuids
     * @param Collection<object> $rawStats
     */
    private function transformRawStats(Collection $listUuids, Collection $rawStats): array
    {
        $initialValue = [
            'assigned_cases_count' => 0,
            'unassigned_cases_count' => 0,
            'completed_cases_count' => 0,
            'archived_cases_count' => 0,
        ];

        $stats = $listUuids
            ->mapWithKeys(static fn (string $uuid) => [$uuid => $initialValue])
            ->toArray();

        foreach ($rawStats as $rawStat) {
            /** @phpstan-ignore-next-line */
            if (!in_array($rawStat->case_list_planner_view, ['assigned', 'unassigned', 'completed', 'archived'], true)) {
                // this should not be possible because of database-triggers
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            /** @phpstan-ignore-next-line */
            $stats[$rawStat->assigned_case_list_uuid][$rawStat->case_list_planner_view . '_cases_count'] += $rawStat->count;
        }

        return $stats;
    }
}
