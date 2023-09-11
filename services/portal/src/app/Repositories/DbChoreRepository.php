<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Dto\Chore\Resource;
use App\Models\Eloquent\Chore;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use App\Repositories\QueryBuilder\DbChoreQueryBuilder;
use App\Services\AuthenticationService;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\Audit\Helpers\AuditEventHelper;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Services\AuditService;
use MinVWS\DBCO\Enum\Models\ResourcePermission;

use function array_map;
use function is_null;
use function usleep;

class DbChoreRepository implements ChoreRepository
{
    public function __construct(
        public AssignmentRepository $assignmentRepository,
        private AuditService $auditService,
        private AuthenticationService $authenticationService,
    ) {
    }

    public function getChore(string $choreId): string
    {
        $chore = Chore::findOrFail($choreId);

        return $chore->uuid;
    }

    public function createChore(
        string $organisationUuid,
        Resource $resource,
        Resource $owner,
        ResourcePermission $requiredPermission,
        ?DateTimeInterface $expiresAt,
    ): string {
        $chore = new Chore();

        $chore->organisation_uuid = $organisationUuid;
        $chore->resource_type = $resource->type;
        $chore->resource_id = $resource->id;
        $chore->resource_permission = $requiredPermission->value;
        $chore->owner_resource_type = $owner->type;
        $chore->owner_resource_id = $owner->id;
        $chore->expires_at = $expiresAt;

        $chore->save();

        return $chore->uuid;
    }

    public function completeChore(string $choreId): void
    {
        $chore = Chore::findOrFail($choreId);

        $this->auditService->registerEvent(AuditEvent::create(
            __METHOD__,
            AuditEvent::ACTION_EXECUTE,
            AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__),
        ), static function () use ($chore): void {
            $chore->delete();
            $chore->assignment?->delete();
        });
    }

    public function cancelChore(string $choreId): void
    {
        $chore = Chore::findOrFail($choreId);

        $this->auditService->registerEvent(AuditEvent::create(
            __METHOD__,
            AuditEvent::ACTION_EXECUTE,
            AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__),
        ), static function () use ($chore): void {
            $chore->delete();
            $chore->assignment?->delete();
        });
    }

    public function cleanupExpiredChores(): void
    {
        Chore::where('expires_at', '<', CarbonImmutable::now())->update(['deleted_at' => CarbonImmutable::now()]);
    }

    public function updateOrganisation(string $choreId, string $organisationUuid): void
    {
        $chore = Chore::findOrFail($choreId);

        if ($chore->organisationUuid === $organisationUuid) {
            return;
        }

        $chore->organisationUuid = $organisationUuid;
        $chore->save();

        $this->removeAssignmentFromChore($chore);
    }

    protected function removeAssignmentFromChore(Chore $chore): void
    {
        $chore->assignment?->delete();
    }

    public function hasAssignment(string $choreId): bool
    {
        $chore = Chore::findOrFail($choreId);

        return $chore->hasAssignment();
    }

    public function userCanAccessResource(Resource $resource, EloquentUser $user, ResourcePermission $permission): bool
    {
        return (new DbChoreQueryBuilder($this->authenticationService, Chore::query()))
            ->whereActive()
            ->whereResource($resource)
            ->whereResourcePermission($permission)
            ->whereAssignedToUser($user)
            ->exists();
    }

    public function findPossiblyDeletedByOwnerResourceId(string $ownerResourceId): ?Chore
    {
        return Chore::query()
            ->where('owner_resource_id', $ownerResourceId)
            ->withTrashed()
            ->first();
    }

    public function forceDeleteChoresFromCaseUuids(array $caseUuids): void
    {
        $versions = array_map(
            static fn (int $version): string => "covid-case-v{$version}",
            EloquentCase::getSchema()->getVersions(),
        );

        Chore::withTrashed()
            ->whereIn('resource_type', $versions)
            ->whereIn('resource_id', $caseUuids)
            ->each($this->doForceDeleteChore(...));
    }

    /**
     * @phptan-param positive-int|0 $usleep
     */
    public function chunkForceDeleteAllChoresWithoutResourceable(
        int $chunkSize,
        int $usleep = 0,
        ?callable $afterEachDelete = null,
    ): bool {
        return Chore::withTrashed()
            ->whereDoesntHave('resourceable')
            ->chunk($chunkSize, function ($collection) use ($afterEachDelete, $usleep): void {
                foreach ($collection as $chore) {
                    $this->doForceDeleteChore($chore);

                    if (!is_null($afterEachDelete)) {
                        $afterEachDelete($chore);
                    }
                }

                if ($usleep > 0) {
                    usleep($usleep);
                }
            });
    }

    private function doForceDeleteChore(Chore $chore): bool|null
    {
        if ($chore->owner_resource_type !== 'call-to-action') {
            return $chore->forceDelete();
        }

        /** @var bool|null */
        return Chore::query()
            ->getConnection()
            ->transaction(static function () use ($chore): bool|null {
                $chore->ownerResourceable()->forceDelete();

                return $chore->forceDelete();
            });
    }
}
