<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Eloquent\EloquentUser;
use App\Models\Policy\PolicyGuideline;
use MinVWS\DBCO\Enum\Models\Permission;

class PolicyGuidelinePolicy
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

    public function delete(EloquentUser $eloquentUser, PolicyGuideline $policyGuideline): bool
    {
        if (!$this->canAccessAdminPolicyAdviceModule($eloquentUser)) {
            return false;
        }

        return $policyGuideline->riskProfiles->count() === 0;
    }

    private function canAccessAdminPolicyAdviceModule(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::adminPolicyAdviceModule()->value);
    }
}
