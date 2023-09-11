<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Eloquent\EloquentUser;
use Illuminate\Auth\Access\Response;
use MinVWS\DBCO\Enum\Models\Permission;

final class CalendarViewPolicy
{
    public function viewAny(EloquentUser $eloquentUser): Response
    {
        return $this->canAccessAdminPolicyAdviceModule($eloquentUser);
    }

    public function view(EloquentUser $eloquentUser): Response
    {
        return $this->canAccessAdminPolicyAdviceModule($eloquentUser);
    }

    public function update(EloquentUser $eloquentUser): Response
    {
        return $this->canAccessAdminPolicyAdviceModule($eloquentUser);
    }

    private function canAccessAdminPolicyAdviceModule(EloquentUser $eloquentUser): Response
    {
        return $eloquentUser->can(Permission::adminPolicyAdviceModule()->value)
            ? Response::allow()
            : Response::deny('User is not allowed to administer CalendarViews.');
    }
}
