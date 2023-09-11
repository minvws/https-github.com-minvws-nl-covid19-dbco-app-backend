<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Dto\Chore\Resource;
use App\Models\Eloquent\Chore;
use App\Models\Eloquent\EloquentUser;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\ResourcePermission;

interface ChoreRepository
{
    public function getChore(string $choreId): string;

    public function createChore(
        string $organisationUuid,
        Resource $resource,
        Resource $owner,
        ResourcePermission $requiredPermission,
        ?DateTimeInterface $expiresAt,
    ): string;

    public function completeChore(string $choreId): void;

    public function cancelChore(string $choreId): void;

    public function cleanupExpiredChores(): void;

    public function updateOrganisation(string $choreId, string $organisationUuid): void;

    public function hasAssignment(string $choreId): bool;

    public function userCanAccessResource(Resource $resource, EloquentUser $user, ResourcePermission $permission): bool;

    public function findPossiblyDeletedByOwnerResourceId(string $ownerResourceId): ?Chore;

    public function forceDeleteChoresFromCaseUuids(array $caseUuids): void;

    /**
     * @phptan-param positive-int|0 $usleep
     */
    public function chunkForceDeleteAllChoresWithoutResourceable(
        int $chunkSize,
        int $usleep = 0,
        ?callable $afterEachDelete = null,
    ): bool;
}
