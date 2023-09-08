<?php

declare(strict_types=1);

namespace App\Services\Chores;

use App\Dto\Chore\Resource;
use App\Exceptions\Chore\UserNotAssignedToCallToActionException;
use App\Models\CallToAction\ListOptions;
use App\Models\Eloquent\CallToAction;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Repositories\CallToActionNoteRepository;
use App\Repositories\CallToActionRepository;
use App\Services\AuthenticationService;
use App\Services\Timeline\TimelineService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\ResourcePermission;

class CallToActionService
{
    public const RESOURCE_TYPE_NAME = 'call-to-action';

    public function __construct(
        private readonly CallToActionRepository $callToActionRepository,
        private readonly AuthenticationService $authenticationService,
        private readonly ChoreService $choreService,
        private readonly CallToActionNoteRepository $callToActionNoteRepository,
        private readonly TimelineService $timelineService,
    ) {
    }

    public function listCallToActions(ListOptions $listOptions, EloquentOrganisation $organisation): LengthAwarePaginator
    {
        return $this->callToActionRepository->listCallToActions($listOptions, $organisation);
    }

    public function getCallToAction(string $uuid): CallToAction
    {
        return $this->callToActionRepository->getCallToAction($uuid, $this->authenticationService->getRequiredSelectedOrganisation());
    }

    public function createCallToAction(
        string $subject,
        string $description,
        string $organisationUuid,
        string $resourceUuid,
        string $resourceType,
        string $resourcePermission,
        ?string $expiresAt,
    ): CallToAction {
        $covidCase = EloquentCase::findOrFail($resourceUuid);
        $callToAction = $this->callToActionRepository->createCallToAction($subject, $description);

        $this->choreService->createChore(
            $organisationUuid,
            new Resource($covidCase->getVersionedResourceType(), $resourceUuid),
            new Resource(self::RESOURCE_TYPE_NAME, $callToAction->uuid),
            ResourcePermission::from($resourcePermission),
            $expiresAt ? CarbonImmutable::make($expiresAt) : null,
        );

        $this->timelineService->addToTimeline($callToAction);

        return $callToAction;
    }

    /**
     * @throws AuthenticationException
     */
    public function pickupCallToAction(CallToAction $callToAction, ?CarbonInterface $expiresAt): string
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $choreUuid = $callToAction->chore->uuid;

        return $this->choreService->assignChore($choreUuid, $user->uuid, $expiresAt ?? CarbonImmutable::now()->addDay());
    }

    /**
     * @throws AuthenticationException
     * @throws UserNotAssignedToCallToActionException
     */
    public function dropCallToAction(CallToAction $callToAction, string $note): void
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $choreUuid = $callToAction->chore->uuid;

        DB::transaction(function () use ($choreUuid, $user, $note, $callToAction): void {
            $assignment = $this->choreService->findAssignmentByChoreAndUser($choreUuid, $user->uuid);

            if (!$assignment) {
                throw new UserNotAssignedToCallToActionException();
            }

            $this->callToActionNoteRepository->createCallToActionNote($note, $callToAction, $user);
            $this->choreService->cancelAssignment($assignment->uuid);
        });
    }

    /**
     * @throws AuthenticationException
     * @throws UserNotAssignedToCallToActionException
     */
    public function completeCallToAction(CallToAction $callToAction, string $note): void
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $choreUuid = $callToAction->chore->uuid;

        DB::transaction(function () use ($choreUuid, $user, $note, $callToAction): void {
            $assignment = $this->choreService->findAssignmentByChoreAndUser($choreUuid, $user->uuid);

            if (!$assignment) {
                throw new UserNotAssignedToCallToActionException();
            }

            $this->choreService->completeChore($choreUuid);
            $this->callToActionNoteRepository->createCallToActionNote($note, $callToAction, $user);
        });
    }

    public function listCallToActionNotes(CallToAction $callToAction, int $limit = 0): Collection
    {
        return $this->callToActionNoteRepository->listCallToActionNotes($callToAction, $limit);
    }
}
