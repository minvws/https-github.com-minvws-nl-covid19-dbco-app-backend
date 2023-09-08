<?php

declare(strict_types=1);

namespace App\Scopes;

use App\Services\AuthenticationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

class CaseListAuthScope implements Scope
{
    public function __construct(
        private readonly AuthenticationService $authService,
    ) {
    }

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('case_list.organisation_uuid', $this->authService->getRequiredSelectedOrganisation()->uuid);
    }

    public function applyToUniqueRule(Unique $rule): Unique
    {
        $rule->where('case_list.organisation_uuid', $this->authService->getRequiredSelectedOrganisation()->uuid)
            ->whereNull('deleted_at');
        return $rule;
    }

    public function applyToExistsRule(Exists $rule): Exists
    {
        $rule->where('case_list.organisation_uuid', $this->authService->getRequiredSelectedOrganisation()->uuid)
            ->whereNull('deleted_at');
        return $rule;
    }
}
