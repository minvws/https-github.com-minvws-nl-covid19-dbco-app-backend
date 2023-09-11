<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DbCaseRepository;
use MinVWS\DBCO\Enum\Models\Permission;
use MinVWS\DBCO\Enum\Models\Priority;

use function auth;

class CasePriorityService
{
    private DbCaseRepository $dbCaseRepository;

    public function __construct(DbCaseRepository $dbCaseRepository)
    {
        $this->dbCaseRepository = $dbCaseRepository;
    }

    public function isPriorityEditAllowed(string $caseUuid): bool
    {
        $user = auth()->user();
        if ($user === null) {
            return false;
        }

        $case = $this->dbCaseRepository->getCaseByUuid($caseUuid);
        if ($case === null) {
            return false;
        }

        return $user->can(Permission::casePlannerEdit()->value, $case);
    }

    public function updateCasePriority(array $caseUuids, Priority $priority): void
    {
        $this->dbCaseRepository->updatePriorityForCases($caseUuids, $priority);
    }
}
