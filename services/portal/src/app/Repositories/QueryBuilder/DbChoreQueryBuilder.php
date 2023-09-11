<?php

declare(strict_types=1);

namespace App\Repositories\QueryBuilder;

use App\Dto\Chore\Resource;
use App\Models\Eloquent\EloquentUser;
use App\Services\AuthenticationService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use MinVWS\DBCO\Enum\Models\ResourcePermission;

class DbChoreQueryBuilder
{
    public const DAYS_VISIBLE_AFTER_EXPIRY = 14;

    public const CHORE_TABLE = 'chore';
    public const ASSIGNMENT_TABLE = 'assignment';

    public function __construct(
        public AuthenticationService $authenticationService,
        public Builder $query,
    ) {
    }

    public function whereActive(): self
    {
        $this->query->where('expires_at', '>', CarbonImmutable::now()->subDays(self::DAYS_VISIBLE_AFTER_EXPIRY));

        return $this;
    }

    public function whereResource(Resource $resource): self
    {
        $this->query
            ->where('resource_id', $resource->id)
            ->where('resource_type', $resource->type);

        return $this;
    }

    public function whereAssignedToUser(EloquentUser $user): self
    {
        $this->query
            ->whereHas(self::ASSIGNMENT_TABLE, static function ($query) use ($user): void {
                $query->where('user_uuid', $user->uuid);
            });

        return $this;
    }

    public function whereResourcePermission(ResourcePermission $permission): self
    {
        $this->query->where('resource_permission', $permission->value);

        return $this;
    }

    public function toQuery(): Builder
    {
        return $this->query;
    }

    public function exists(): bool
    {
        return $this->query->exists();
    }
}
