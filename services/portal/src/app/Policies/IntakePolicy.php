<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\Intake;
use Illuminate\Auth\Access\HandlesAuthorization;
use MinVWS\DBCO\Enum\Models\Permission;

class IntakePolicy
{
    use HandlesAuthorization;

    public function list(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::intakeList()->value);
    }

    public function view(EloquentUser $eloquentUser, Intake $intake): bool
    {
        return true;
    }
}
