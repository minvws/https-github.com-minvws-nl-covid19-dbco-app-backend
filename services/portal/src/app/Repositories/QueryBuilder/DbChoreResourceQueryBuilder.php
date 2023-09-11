<?php

declare(strict_types=1);

namespace App\Repositories\QueryBuilder;

use App\Models\CallToAction\ListOptions;
use App\Models\Eloquent\EloquentOrganisation;
use App\Services\AuthenticationService;
use App\Services\Chores\CallToActionService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use MinVWS\DBCO\Enum\Models\ChoreResourceType;

use function sprintf;

class DbChoreResourceQueryBuilder
{
    public const DAYS_VISIBLE_AFTER_EXPIRY = DbChoreQueryBuilder::DAYS_VISIBLE_AFTER_EXPIRY;

    public function __construct(
        public AuthenticationService $authenticationService,
        public Builder $query,
    ) {
        $this->query
            ->join('chore', static function ($join): void {
                $join->on('call_to_action.uuid', '=', 'chore.owner_resource_id')
                    ->where('chore.owner_resource_type', '=', CallToActionService::RESOURCE_TYPE_NAME);
            })
            ->leftJoin('covidcase', 'chore.resource_id', '=', 'covidcase.uuid')
            ->whereNotNull('chore.uuid')
            ->select('call_to_action.*');
    }

    public function joinMyAssigned(): self
    {
        $this->query
            ->leftJoin('assignment', static function (JoinClause $leftJoin): void {
                $leftJoin->on('chore.uuid', '=', 'assignment.chore_uuid')
                    ->where('assignment.deleted_at', '=', null)
                    ->where(static function ($query): void {
                        $query
                            ->where('assignment.expires_at', '>', CarbonImmutable::now())
                            ->orWhereNull('assignment.expires_at');
                    });
            })
            ->where('assignment.user_uuid', '=', $this->authenticationService->getAuthenticatedUser()->uuid);

        return $this;
    }

    public function joinUnAssigned(): self
    {
        $this->query
            ->leftJoin('assignment', static function (JoinClause $leftJoin): void {
                $leftJoin->on('chore.uuid', '=', 'assignment.chore_uuid')
                    ->where('assignment.deleted_at', '=', null)
                    ->where(static function ($query): void {
                        $query
                            ->where('assignment.expires_at', '>', CarbonImmutable::now())
                            ->orWhereNull('assignment.expires_at');
                    });
            })
            ->whereNull('assignment.chore_uuid');

        return $this;
    }

    public function whereOrganisation(EloquentOrganisation $organisation): self
    {
        $this->query->where('chore.organisation_uuid', '=', $organisation->uuid);
        $this->query->whereNull('chore.deleted_at');

        return $this;
    }

    public function whereActive(): self
    {
        $this->query->where('chore.expires_at', '>', CarbonImmutable::now()->subDays(self::DAYS_VISIBLE_AFTER_EXPIRY));

        return $this;
    }

    public function whereResourceTypeNotDeleted(ChoreResourceType $resource): self
    {
        $this->query
            ->where(static function (Builder $query) use ($resource): void {
                $query
                    ->where(static function (Builder $query) use ($resource): void {
                        $query
                            ->where('resource_type', 'like', sprintf('%s%%', $resource->value))
                            ->whereNull('covidcase.deleted_at');
                    })
                ->orWhereNot('resource_type', 'like', sprintf('%s%%', $resource->value));
            });

        return $this;
    }

    public function orderByCallToActionListOptions(ListOptions $listOptions): self
    {
        $this->query
            ->orderBy('assignment.user_uuid', 'desc')
            ->orderBy($listOptions->sort ?? 'chore.expires_at', $listOptions->order ?? 'asc');

        return $this;
    }

    public function toQuery(): Builder
    {
        return $this->query;
    }
}
