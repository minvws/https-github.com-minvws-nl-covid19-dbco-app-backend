<?php

declare(strict_types=1);

namespace App\Scopes;

use App\Models\Eloquent\EloquentUser;
use App\Models\Export\ExportClient;
use App\Services\Assignment\Enum\AssignmentModelEnum;
use App\Services\Assignment\HasAssignmentToken;
use App\Services\Assignment\TokenResource;
use App\Services\AuthenticationService;
use App\Services\Chores\ChoreService;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

use function array_merge;
use function assert;
use function count;

class CaseAuthScope implements Scope
{
    private readonly EloquentUser|ExportClient|null $user;
    private array $assignedChoreUuids;

    public function __construct(
        private readonly AuthenticationService $authService,
        private readonly ChoreService $choreService,
        private readonly bool $runningInConsole,
        readonly Guard $guard,
    ) {
        $this->user = $guard->user();
    }

    public function apply(Builder $builder, Model $model): void
    {
        if ($this->runningInConsole && !$this->authService->isLoggedIn()) {
            return;
        }

        if ($this->user instanceof ExportClient) {
            // ExportClient uses its own organisation filtering
            return;
        }

        $selectedOrganisation = $this->authService->getSelectedOrganisation();
        if ($selectedOrganisation === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where(function (Builder $query) use ($selectedOrganisation): void {
            $query
                ->where('covidcase.organisation_uuid', $selectedOrganisation->uuid)
                ->orWhere('covidcase.assigned_organisation_uuid', $selectedOrganisation->uuid)
                ->when(
                    $this->hasAdditionalUuids(),
                    function (Builder $query): void {
                        $query->orWhereIn('covidcase.uuid', $this->getAdditionalUuids());
                    },
                );
        });
    }

    private function hasAdditionalUuids(): bool
    {
        return $this->hasTokenCaseUuids() || $this->hasAssignedChoreUuids();
    }

    /**
     * @return array<string>
     */
    private function getAdditionalUuids(): array
    {
        return array_merge(
            $this->getTokenCaseUuids(),
            $this->getAssignedChoreUuids(),
        );
    }

    private function hasAssignedChoreUuids(): bool
    {
        return count($this->getAssignedChoreUuids()) !== 0;
    }

    /**
     * @return array<string>
     */
    private function getAssignedChoreUuids(): array
    {
        if (!isset($this->assignedChoreUuids)) {
            $this->assignedChoreUuids = $this->user instanceof EloquentUser
                ? $this->choreService->getCaseUuidsByAssignmentToUser($this->user)
                : [];
        }

        return $this->assignedChoreUuids;
    }

    private function hasTokenCaseUuids(): bool
    {
        if (!$this->user instanceof HasAssignmentToken) {
            return false;
        }

        if (!$this->user->hasToken()) {
            return false;
        }

        $caseResource = $this->user
            ->getToken()
            ->res
            ->first(static fn (TokenResource $resource): bool => $resource->mod === AssignmentModelEnum::Case_);

        return $caseResource !== null && count($caseResource->ids);
    }

    /**
     * @return array<string>
     */
    private function getTokenCaseUuids(): array
    {
        if (!$this->hasTokenCaseUuids()) {
            return [];
        }

        assert($this->user instanceof HasAssignmentToken);

        if (!$this->user->hasToken()) {
            return [];
        }

        return $this->user
            ->getToken()
            ->res
            ->first(static fn (TokenResource $resource): bool => $resource->mod === AssignmentModelEnum::Case_)
            ?->ids ?? [];
    }
}
