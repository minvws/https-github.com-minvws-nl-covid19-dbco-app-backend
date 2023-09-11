<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Assignment\NullCaseListAssignment;
use App\Models\CaseList\ListOptions;
use App\Models\Eloquent\CaseList;
use App\Repositories\CaseListRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CaseListService
{
    private CaseListRepository $caseListRepository;
    private AuthenticationService $authService;
    private CaseAssignmentService $assignmentService;

    public function __construct(
        CaseListRepository $caseListRepository,
        AuthenticationService $authService,
        CaseAssignmentService $assignmentService,
    ) {
        $this->caseListRepository = $caseListRepository;
        $this->authService = $authService;
        $this->assignmentService = $assignmentService;
    }

    public function listCaseLists(ListOptions $listOptions): LengthAwarePaginator
    {
        return $this->caseListRepository->listCaseLists($listOptions);
    }

    public function getDefaultCaseQueue(bool $stats): ?CaseList
    {
        return $this->caseListRepository->getDefaultCaseQueue($stats);
    }

    public function getCaseListByUuid(string $uuid, bool $stats): ?CaseList
    {
        return $this->caseListRepository->getCaseListByUuid($uuid, $stats);
    }

    public function createCaseList(CaseList $caseList): bool
    {
        $caseList->organisation_uuid = $this->getOrganisationUuid();
        $caseList->is_default = false;
        $caseList->is_queue = false;
        return $this->caseListRepository->createCaseList($caseList);
    }

    public function updateCaseList(CaseList $caseList): bool
    {
        return $this->caseListRepository->updateCaseList($caseList);
    }

    public function deleteCaseList(CaseList $caseList): bool
    {
        $uuids = [];
        foreach ($caseList->cases as $case) {
            $uuids[] = $case->uuid;
        }

        $this->assignmentService->assignCases($uuids, new NullCaseListAssignment());

        return $this->caseListRepository->deleteCaseList($caseList);
    }

    private function getOrganisationUuid(): string
    {
        return $this->authService->getRequiredSelectedOrganisation()->uuid;
    }
}
