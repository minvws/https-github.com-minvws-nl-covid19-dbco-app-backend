<?php

declare(strict_types=1);

namespace App\Services\Chores;

use App\Dto\Chore\Resource;
use App\Exceptions\Chore\ChoreAssignedException;
use App\Models\Eloquent\Assignment;
use App\Models\Eloquent\Chore;
use App\Models\Eloquent\EloquentUser;
use App\Repositories\AssignmentRepository;
use App\Repositories\ChoreRepository;
use App\Services\AuthenticationService;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Auth\AuthenticationException;
use MinVWS\DBCO\Enum\Models\ResourcePermission;

class ChoreService
{
    protected ChoreRepository $choreRepository;
    protected AssignmentRepository $assignmentRepository;
    protected AuthenticationService $authenticationService;

    public function __construct(
        ChoreRepository $choreRepository,
        AssignmentRepository $assignmentRepository,
        AuthenticationService $authenticationService,
    ) {
        $this->choreRepository = $choreRepository;
        $this->assignmentRepository = $assignmentRepository;
        $this->authenticationService = $authenticationService;
    }

    public function createChore(string $organisationUuid, Resource $resource, Resource $owner, ResourcePermission $requiredPermission, ?DateTimeInterface $expiresAt): string
    {
        return $this->choreRepository->createChore($organisationUuid, $resource, $owner, $requiredPermission, $expiresAt);
    }

    public function assignChore(string $choreId, string $userUuid, ?CarbonInterface $expiresAt): string
    {
        if ($this->choreRepository->hasAssignment($choreId)) {
            throw new ChoreAssignedException();
        }

        return $this->assignmentRepository->createAssignment($choreId, $userUuid, $expiresAt);
    }

    public function findAssignmentByChoreAndUser(string $choreId, string $userUuid): ?Assignment
    {
        return $this->assignmentRepository->findByChoreAndUser($choreId, $userUuid);
    }

    public function completeChore(string $choreId): void
    {
        $this->choreRepository->completeChore($choreId);
    }

    public function cancelChore(string $choreId): void
    {
        $this->choreRepository->cancelChore($choreId);
    }

    public function cancelAssignment(string $assignmentId): void
    {
        $this->assignmentRepository->deleteAssignment($assignmentId);
    }

    public function cleanupExpiredChoresAndAssignments(): void
    {
        $this->choreRepository->cleanupExpiredChores();
        $this->assignmentRepository->cleanupExpiredAssignments();
    }

    /**
     * @throws AuthenticationException
     */
    public function canAccessResource(ResourcePermission $permission, Resource $resource, ?EloquentUser $user = null): bool
    {
        $user = $user ?? $this->authenticationService->getAuthenticatedUser();

        return $this->choreRepository->userCanAccessResource($resource, $user, $permission);
    }

    public function updateOrganisation(string $choreId, string $organisationUuid): void
    {
        $this->choreRepository->updateOrganisation($choreId, $organisationUuid);
    }

    public function findPossiblyDeletedChoreByOwnerResourceId(string $ownerResourceId): ?Chore
    {
        return $this->choreRepository->findPossiblyDeletedByOwnerResourceId($ownerResourceId);
    }

    /** @return array<string> */
    public function getCaseUuidsByAssignmentToUser(EloquentUser $user): array
    {
        return $this->assignmentRepository->findCaseUuidsByAssignmentToUser($user);
    }

    public function forceDeleteByCaseUuids(array $caseUuids): void
    {
        $this->choreRepository->forceDeleteChoresFromCaseUuids($caseUuids);
    }

    public function chunkForceDeleteAllChoresWithoutResourceable(
        int $chunkSize,
        int $usleep = 0,
        ?callable $afterEachDelete = null,
    ): bool {
        return $this->choreRepository
            ->chunkForceDeleteAllChoresWithoutResourceable($chunkSize, $usleep, $afterEachDelete);
    }
}
