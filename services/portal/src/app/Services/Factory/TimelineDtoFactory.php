<?php

declare(strict_types=1);

namespace App\Services\Factory;

use App\Dto\CallToActionTimelineDto;
use App\Dto\ExpertQuestionTimelineDto;
use App\Dto\TimelineDto;
use App\Helpers\TimezoneAware;
use App\Models\Eloquent\CallToAction;
use App\Models\Eloquent\CaseAssignmentHistory;
use App\Models\Eloquent\Chore;
use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\ExpertQuestion;
use App\Models\Eloquent\Note;
use App\Models\Eloquent\Timeline;
use App\Repositories\ChoreRepository;
use App\Services\AuthenticationService;
use App\Services\Timeline\AssignmentChangeBuilder;
use App\Services\Timeline\AssignmentMessageService;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;

use function implode;
use function is_null;
use function reset;
use function sprintf;

class TimelineDtoFactory
{
    public function __construct(
        private readonly AssignmentChangeBuilder $assignmentChangeBuilder,
        private readonly AssignmentMessageService $assignmentMessageService,
        private readonly AuthenticationService $authService,
        private readonly ChoreRepository $choreRepository,
    ) {
    }

    public function fromNote(Note $note): TimelineDto
    {
        $timeline = $note->timeline;

        return new TimelineDto(
            $timeline->uuid,
            $note->user_name . ', ' . $note->organisation_name,
            $note->note,
            $this->buildNoteTime($note),
            $note->uuid,
            'note',
        );
    }

    public function fromCaseAssignmentHistory(CaseAssignmentHistory $assignmentHistory, Collection $timelines): TimelineDto
    {
        $timeline = $assignmentHistory->timeline;

        $previousAssigment = $this->getPreviousAssignment($assignmentHistory, $timelines);

        $assigner = $assignmentHistory->assignedBy;
        $notes = ['Toewijzing van de case is veranderd' . (is_null($assigner) ? "" : " door <b>{$assigner->name}</b>")];

        foreach ($this->assignmentChangeBuilder->getAssignmentChanges($assignmentHistory, $previousAssigment) as $change) {
            $notes[] = $this->assignmentMessageService->buildMessage($change, $this->authService->getAuthenticatedUser());
        }

        return new TimelineDto(
            $timeline->uuid,
            'Toewijzing',
            implode('<br>', $notes),
            $this->buildAssigmentTime($assignmentHistory, $previousAssigment),
            $assignmentHistory->uuid,
            'case-assignment-history',
        );
    }

    public function fromExpertQuestion(ExpertQuestion $expertQuestion): ExpertQuestionTimelineDto
    {
        $timeline = $expertQuestion->timeline;

        return new ExpertQuestionTimelineDto(
            $timeline->uuid,
            $expertQuestion->subject,
            $expertQuestion->question,
            $this->buildExpertQuestionTime($expertQuestion),
            $expertQuestion->uuid,
            'expert-question',
            $expertQuestion->user->name,
            $expertQuestion->answer->answer ?? null,
            $expertQuestion->answer !== null ? $this->buildAnswerUsernameWithRoles($expertQuestion->answer->answeredBy) : null,
            $this->buildExpertQuestionWithAnswerTime($expertQuestion),
        );
    }

    public function fromCallToAction(CallToAction $callToAction): CallToActionTimelineDto
    {
        $timeline = $callToAction->timeline;
        /** @var EloquentUser $user */
        $user = EloquentUser::findOrFail($callToAction->created_by);
        $chore = $this->choreRepository->findPossiblyDeletedByOwnerResourceId($callToAction->uuid);

        return new CallToActionTimelineDto(
            $timeline->uuid,
            $callToAction->subject,
            $callToAction->description,
            $this->buildCallToActionTime($callToAction),
            $callToAction->uuid,
            'call-to-action',
            $user->name,
            $callToAction->uuid,
            $this->buildChoreExpiresAtTime($chore),
        );
    }

    public function fromConflictingCaseAssignmentHistory(CaseAssignmentHistory $assignmentHistory, Collection $timelines): TimelineDto
    {
        $timeline = $assignmentHistory->timeline;
        $previousAssigment = $this->getPreviousAssignment($assignmentHistory, $timelines);
        $changes = $this->assignmentChangeBuilder->getAssignmentChanges($assignmentHistory, $previousAssigment);
        $change = reset($changes);
        $note = $change
            ? $this->assignmentMessageService->buildConflictMessage(
                $change,
                $this->authService->getAuthenticatedUser(),
            )
            : '';

        return new TimelineDto(
            $timeline->uuid,
            'Toewijzing',
            $note,
            $this->buildAssigmentTime($assignmentHistory, $previousAssigment),
            $assignmentHistory->uuid,
            'case-assignment-history',
        );
    }

    private function buildNoteTime(Note $note): string
    {
        return sprintf(
            '%s  om  %s • %s',
            TimezoneAware::isoFormat($note->created_at, 'LL'),
            TimezoneAware::format($note->created_at, 'H:i'),
            $note->type->label,
        );
    }

    private function buildAssigmentTime(
        CaseAssignmentHistory $assignmentHistory,
        ?CaseAssignmentHistory $previousAssignment,
    ): string {
        if ($previousAssignment === null) {
            return $this->buildDateTimeString($assignmentHistory->assigned_at);
        }

        $diffInSeconds = $assignmentHistory->assigned_at->diffInSeconds($previousAssignment->assigned_at);
        $diff = $diffInSeconds < 60
            ? CarbonInterval::seconds($diffInSeconds)
            : CarbonInterval::minutes($assignmentHistory->assigned_at->diffInMinutes($previousAssignment->assigned_at));

        return sprintf(
            '%s • Gedurende %s',
            $this->buildDateTimeString($assignmentHistory->assigned_at),
            $diff->cascade()->forHumans(),
        );
    }

    private function buildCallToActionTime(CallToAction $callToAction): string
    {
        return $this->buildDateTimeString($callToAction->created_at);
    }

    private function buildChoreExpiresAtTime(?Chore $chore): string
    {
        if (!$chore || !$chore->expires_at) {
            return '';
        }

        return TimezoneAware::isoFormat($chore->expires_at, 'LL');
    }

    private function buildExpertQuestionTime(ExpertQuestion $expertQuestion): string
    {
        return sprintf(
            '%s • Hulpvraag aan %s',
            $this->buildDateTimeString($expertQuestion->created_at),
            $expertQuestion->type->label,
        );
    }

    private function buildExpertQuestionWithAnswerTime(ExpertQuestion $expertQuestion): ?string
    {
        if (!$expertQuestion->answer) {
            return null;
        }

        return $this->buildDateTimeString($expertQuestion->answer->created_at);
    }

    /**
     * @param Collection<array-key,Timeline> $timelines
     */
    private function getPreviousAssignment(CaseAssignmentHistory $assignmentHistory, Collection $timelines): ?CaseAssignmentHistory
    {
        $assignments = $timelines->filter(
            static fn(Timeline $timeline) => $timeline->timelineable_type === $assignmentHistory->getMorphClass()
        )
            ->sortBy(static fn(Timeline $timeline) => $timeline->created_at);

        $timelineUuid = $assignmentHistory->timeline->uuid;
        $previousAssigment = null;
        $assignments->each(static function (Timeline $timeline) use (&$previousAssigment, $timelineUuid) {
            if ($timelineUuid === $timeline->uuid) {
                return false;
            }

            $previousAssigment = $timeline->timelineable;
            return true;
        });

        return $previousAssigment;
    }

    private function buildAnswerUsernameWithRoles(?EloquentUser $user): ?string
    {
        if (!is_null($user)) {
            $roles = [];

            if ($user->hasRole('conversation_coach')) {
                $roles[] = 'Gesprekscoach';
            }

            if ($user->hasRole('conversation_coach_nationwide')) {
                $roles[] = 'Gesprekscoach Landelijke Schil';
            }

            if ($user->hasRole('medical_supervisor')) {
                $roles[] = 'Medisch supervisor';
            }

            if ($user->hasRole('medical_supervisor_nationwide')) {
                $roles[] = 'Medisch supervisor Landelijke Schil';
            }

            return $user->name . ', ' . implode(', ', $roles);
        }

        return null;
    }

    private function buildDateTimeString(CarbonInterface $dateTime): string
    {
        return sprintf(
            '%s  om  %s',
            TimezoneAware::isoFormat($dateTime, 'LL'),
            TimezoneAware::format($dateTime, 'H:i'),
        );
    }
}
