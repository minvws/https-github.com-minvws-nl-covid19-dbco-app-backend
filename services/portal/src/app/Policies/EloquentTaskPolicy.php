<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\EloquentUser;
use App\Policies\Traits\AccessibleByCase;
use MinVWS\DBCO\Enum\Models\Permission;

use function app;

class EloquentTaskPolicy
{
    use AccessibleByCase;

    public function create(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::taskCreate()->value);
    }

    public function delete(EloquentUser $eloquentUser, EloquentTask $eloquentTask): bool
    {
        // A task always has a covidCase, but the CaseAuthScope prevents it from loading
        if (!$eloquentTask->covidCase instanceof EloquentCase) {
            return false;
        }

        if (
            $eloquentUser->can(Permission::taskComplianceDelete()->value)
            && $this->viewAccessRequest($eloquentUser, $eloquentTask)
        ) {
            return true;
        }

        if (!$eloquentUser->can(Permission::taskUserDelete()->value)) {
            return false;
        }

        if ($eloquentTask->covidCase === null) {
            return false;
        }

        return $this->canEditCase($eloquentUser, $eloquentTask->covidCase);
    }

    public function view(EloquentUser $eloquentUser, EloquentTask $eloquentTask): bool
    {
        if ($this->edit($eloquentUser, $eloquentTask)) {
            return true;
        }

        if (!$eloquentTask->covidCase instanceof EloquentCase) {
            return false;
        }

        return $this->canViewCase($eloquentUser, $eloquentTask->covidCase);
    }

    public function edit(EloquentUser $eloquentUser, EloquentTask $eloquentTask): bool
    {
        if (!$eloquentUser->can(Permission::taskEdit()->value)) {
            return false;
        }

        if (!$eloquentTask->covidCase instanceof EloquentCase) {
            return false;
        }

        return $this->canEditCase($eloquentUser, $eloquentTask->covidCase);
    }

    public function restore(EloquentUser $eloquentUser, EloquentTask $task): bool
    {
        if (!$eloquentUser->can(Permission::taskRestore()->value)) {
            return false;
        }

        return $this->viewAccessRequest($eloquentUser, $task);
    }

    public function viewAccessRequest(EloquentUser $eloquentUser, EloquentTask $task): bool
    {
        /** @var AccessRequestPolicy $accessRequestPolicy */
        $accessRequestPolicy = app(AccessRequestPolicy::class);
        return $accessRequestPolicy->viewAccessRequestTask($eloquentUser, $task);
    }
}
