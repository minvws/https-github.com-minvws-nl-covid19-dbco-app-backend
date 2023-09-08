<?php

declare(strict_types=1);

namespace App\Scopes;

use App\Exceptions\RequiredOrganisationNotFoundException;
use App\Models\Eloquent\EloquentUser;
use App\Models\Export\ExportClient;
use App\Services\AuthenticationService;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrganisationAuthScope implements Scope
{
    private readonly EloquentUser|ExportClient|null $user;

    public function __construct(
        private AuthenticationService $authService,
        readonly Guard $guard,
    )
    {
        $this->user = $guard->user();
    }

    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model)
    {
        if ($this->user instanceof ExportClient) {
            // ExportClient uses its own organisation filtering
            return;
        }

        try {
            $builder->where('organisation.uuid', $this->authService->getRequiredSelectedOrganisation()->uuid);
        } catch (RequiredOrganisationNotFoundException) {
            // if not logged in, no scope is applied
            return;
        }
    }
}
