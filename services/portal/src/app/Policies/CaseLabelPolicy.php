<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Eloquent\EloquentUser;
use MinVWS\DBCO\Enum\Models\Permission;

class CaseLabelPolicy
{
    public function list(EloquentUser $eloquentUser): bool
    {
        if ($eloquentUser->can(Permission::caseUserEdit()->value)) {
            return true;
        }

        return $eloquentUser->can(Permission::casePlannerEdit()->value);
    }
}
