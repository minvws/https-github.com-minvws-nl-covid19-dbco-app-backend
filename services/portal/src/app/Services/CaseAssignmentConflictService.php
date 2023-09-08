<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\CaseAssignmentConflict;
use App\Models\Eloquent\CaseAssignmentHistory;
use App\Repositories\CaseAssignmentHistoryRepository;
use App\Services\Factory\TimelineDtoFactory;
use App\Services\Timeline\TimelineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

use function count;

class CaseAssignmentConflictService
{
    private CaseAssignmentHistoryRepository $caseAssignmentHistoryRepository;

    private TimelineDtoFactory $timelineDtoFactory;

    private TimelineService $timelineService;

    public function __construct(
        CaseAssignmentHistoryRepository $caseAssignmentHistoryRepository,
        TimelineDtoFactory $timelineDtoFactory,
        TimelineService $timelineService,
    ) {
        $this->caseAssignmentHistoryRepository = $caseAssignmentHistoryRepository;
        $this->timelineDtoFactory = $timelineDtoFactory;
        $this->timelineService = $timelineService;
    }

    private function createUpdateAssignmentResponseStatus(
        array $caseUuids,
        Collection $errors,
        Collection $conflicts,
    ): int {
        if (count($caseUuids) <= 1) {
            return $errors->isEmpty() ? Response::HTTP_OK : Response::HTTP_CONFLICT;
        }

        if (count($caseUuids) === $conflicts->count()) {
            return Response::HTTP_CONFLICT;
        }

        if ($conflicts->count() > 0) {
            return Response::HTTP_OK;
        }

        return Response::HTTP_NO_CONTENT;
    }

    /**
     * @return Collection<int,CaseAssignmentHistory>
     */
    public function findConflictingAssignments(array $covidCaseUuids, string $staleSince): Collection
    {
        return $this->caseAssignmentHistoryRepository
            ->findByCaseUuidAssignedSince($covidCaseUuids, $staleSince)
            ->unique(CaseAssignmentHistory::COLUMN_CASE_UUID);
    }

    /**
     * @param Collection<array-key,CaseAssignmentHistory> $conflicts
     */
    public function createUpdateAssignmentResponse(
        array $caseUuids,
        Collection $conflicts,
    ): JsonResponse {
        $errors = $conflicts->map(function (CaseAssignmentHistory $history): CaseAssignmentConflict {
            $timeline = $this->timelineService->getTimeline($history->case);
            $timelineDto = $this->timelineDtoFactory->fromConflictingCaseAssignmentHistory($history, $timeline);
            return new CaseAssignmentConflict($history->case->caseId ?? '', $timelineDto->getNote() ?? '');
        });

        $status = $this->createUpdateAssignmentResponseStatus($caseUuids, $errors, $conflicts);

        return new JsonResponse($errors, $status);
    }
}
