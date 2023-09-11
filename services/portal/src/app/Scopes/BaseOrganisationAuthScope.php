<?php

declare(strict_types=1);

namespace App\Scopes;

use App\Services\AuthenticationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

use function app;

class BaseOrganisationAuthScope implements Scope
{
    private AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model)
    {
        if (app()->runningInConsole() && !$this->authService->isLoggedIn()) {
            return;
        }

        $builder->where('organisation_uuid', $this->authService->getRequiredSelectedOrganisation()->uuid);
    }
}
