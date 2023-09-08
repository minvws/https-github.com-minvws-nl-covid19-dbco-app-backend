<?php

declare(strict_types=1);

namespace App\Services\Chores;

use App\Dto\CallToActionHistory\CallToActionHistoryDto;
use App\Dto\CallToActionHistory\EventDto;
use App\Models\Eloquent\Assignment;
use App\Models\Eloquent\CallToAction;
use App\Models\Eloquent\CallToActionNote;
use App\Models\Eloquent\Chore;
use App\Models\Eloquent\EloquentCase;
use App\Repositories\AssignmentRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\CallToActionEvent;

class CallToActionHistoryService
{
    private const QUERY_LIMIT = 10;

    public function __construct(
        private CallToActionService $callToActionService,
        private AssignmentRepository $assignmentRepository,
    ) {
    }

    public function getCallToActionHistoryForCase(EloquentCase $eloquentCase): Collection
    {
        $caseChores = $eloquentCase->chores()->withTrashed()->limit(self::QUERY_LIMIT)->get();

        return $caseChores->map(function (Chore $chore): CallToActionHistoryDto {
            return $this->getCallToActionHistoryForChore($chore);
        });
    }

    public function getCallToActionHistoryForChore(Chore $chore): CallToActionHistoryDto
    {
        /** @var CallToAction $callToAction */
        $callToAction = $chore->ownerResourceable;

        $assignments = $this->assignmentRepository->listChoreAssignments($chore->uuid, self::QUERY_LIMIT);
        /** @var Collection<int, CallToActionNote> $notes */
        $notes = $this->callToActionService->listCallToActionNotes($callToAction, self::QUERY_LIMIT);

        $assignmentEvents = new Collection();

        /** @var Assignment $assignment */
        foreach ($assignments as $key => $assignment) {
            $assignmentEvents->push(new EventDto(CallToActionEvent::pickedUp(), $assignment->user, $assignment->created_at));

            if ($assignment->deleted_at) {
                $callToActionEvent = $this->getCallToActionEvent($chore, $key);

                $assignmentEvents->push(new EventDto($callToActionEvent, $assignment->user, $assignment->deleted_at));
            }

            if (
                $assignment->deleted_at === null
                && ($assignment->expires_at !== null
                && $assignment->expires_at < CarbonImmutable::now())
            ) {
                $assignmentEvents->push(new EventDto(CallToActionEvent::expired(), $assignment->user, $assignment->expires_at));
            }
        }

        $noteEvents = new Collection(
            $notes->map(
                static fn (CallToActionNote $note): EventDto => new EventDto(
                    CallToActionEvent::note(),
                    $note->user,
                    $note->created_at,
                    $note->note,
                )
            )->toArray(),
        );

        $criteria = [static fn (EventDto $a, EventDto $b): int => $b->dateTime <=> $a->dateTime];
        /** @phpstan-ignore-next-line */
        $events = $noteEvents->merge($assignmentEvents)->sortBy($criteria);

        return new CallToActionHistoryDto(
            $events,
            $chore->created_at,
            $chore->expires_at,
            $chore->deleted_at,
            $callToAction->subject,
            $callToAction->description,
            $callToAction->createdBy?->roles,
        );
    }

    private function getCallToActionEvent(Chore $chore, int|string $key): CallToActionEvent
    {
        $choreIsCompletedByUser = $chore->deleted_at !== null && // chore must be deleted / completed
            $key === 0 && // the most recent assignment
            ( // the assignment must be removed by a user action, not by automated clean-up
                $chore->expires_at === null ||
                CarbonImmutable::parse($chore->deleted_at)->lt(CarbonImmutable::parse($chore->expires_at))
            );

        return $choreIsCompletedByUser
            ? CallToActionEvent::completed()
            : CallToActionEvent::returned();
    }
}
