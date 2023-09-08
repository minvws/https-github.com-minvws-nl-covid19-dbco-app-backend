<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Eloquent\EloquentUser;
use App\Models\Policy\RiskProfile;
use MinVWS\DBCO\Enum\Models\Permission;

class RiskProfilePolicy
{
    public function viewAny(EloquentUser $eloquentUser): bool
    {
        return $this->canAccessAdminPolicyAdviceModule($eloquentUser);
    }

    public function view(EloquentUser $eloquentUser): bool
    {
        return $this->canAccessAdminPolicyAdviceModule($eloquentUser);
    }

    public function update(EloquentUser $eloquentUser): bool
    {
        return $this->canAccessAdminPolicyAdviceModule($eloquentUser);
    }

    public function delete(EloquentUser $eloquentUser, RiskProfile $riskProfile): bool
    {
        if (!$this->canAccessAdminPolicyAdviceModule($eloquentUser)) {
            return false;
        }

        return !$riskProfile->is_active;
    }

    private function canAccessAdminPolicyAdviceModule(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::adminPolicyAdviceModule()->value);
    }
}
