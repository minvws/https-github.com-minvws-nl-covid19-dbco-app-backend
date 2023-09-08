<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\EloquentUser;
use MinVWS\DBCO\Enum\Models\Permission;

class AccessRequestPolicy
{
    public function list(EloquentUser $eloquentUser): bool
    {
        if (!$eloquentUser->can(Permission::caseListAccessRequests()->value)) {
            return false;
        }

        return !$eloquentUser->organisations->isEmpty();
    }

    public function viewAccessRequestCase(EloquentUser $eloquentUser, EloquentCase $case): bool
    {
        if ($eloquentUser->can(Permission::caseViewAccessRequest()->value) === false) {
            return false;
        }

        return $eloquentUser->getRequiredOrganisation()->uuid === $case->organisation_uuid;
    }

    public function viewAccessRequestTask(EloquentUser $eloquentUser, EloquentTask $task): bool
    {
        if ($eloquentUser->can(Permission::taskViewAccessRequest()->value) === false) {
            return false;
        }

        // A task always has a covidCase, but the CaseAuthScope prevents it from loading
        if (!$task->covidCase instanceof EloquentCase) {
            return false;
        }

        return $eloquentUser->getRequiredOrganisation()->uuid === $task->covidCase->organisation_uuid;
    }
}
