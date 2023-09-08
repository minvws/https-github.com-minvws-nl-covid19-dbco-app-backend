<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use App\Policies\Traits\AccessibleByCase;
use MinVWS\DBCO\Enum\Models\Permission;

class ContextPolicy
{
    use AccessibleByCase;

    public function create(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::contextCreate()->value);
    }

    public function delete(EloquentUser $eloquentUser, Context $context): bool
    {
        if (!$eloquentUser->can(Permission::contextDelete()->value)) {
            return false;
        }

        if (!$context->case instanceof EloquentCase) {
            return false;
        }

        return $this->canEditCase($eloquentUser, $context->case);
    }

    public function view(EloquentUser $eloquentUser, Context $context): bool
    {
        if ($this->edit($eloquentUser, $context)) {
            return true;
        }

        if (!$context->case instanceof EloquentCase) {
            return false;
        }

        return $this->canViewCase($eloquentUser, $context->case);
    }

    public function edit(EloquentUser $eloquentUser, Context $context): bool
    {
        if (!$eloquentUser->can(Permission::contextEdit()->value)) {
            return false;
        }

        if (!$context->case instanceof EloquentCase) {
            return false;
        }

        return $this->canEditCase($eloquentUser, $context->case);
    }

    public function link(EloquentUser $eloquentUser, Context $context): bool
    {
        if (!$eloquentUser->can(Permission::contextLink()->value)) {
            return false;
        }

        return $eloquentUser->can(Permission::caseUserEdit()->value, $context->case());
    }
}
