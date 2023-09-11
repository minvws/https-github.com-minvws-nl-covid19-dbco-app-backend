<?php

declare(strict_types=1);

namespace App\Services\Timeline;

use App\Models\Eloquent\CaseAssignmentHistory;
use App\Models\Eloquent\CaseList;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use App\Scopes\CaseListAuthScope;
use App\Scopes\OrganisationAuthScope;
use App\Services\Timeline\ValueObject\AssignmentHistory;
use RuntimeException;

class AssignmentMessageService
{
    public function buildMessage(AssignmentHistory $change, EloquentUser $user): string
    {
        switch ($change->getType()) {
            case AssignmentHistory::TYPE_ORGANISATION:
                return $this->buildOrganisationMessage($change);
            case AssignmentHistory::TYPE_LIST:
                return $this->buildListMessage($change, $user);
            case AssignmentHistory::TYPE_USER:
                return $this->buildUserMessage($change, $user);
        }

        return '';
    }

    public function buildConflictMessage(AssignmentHistory $change, EloquentUser $user): string
    {
        switch ($change->getType()) {
            case AssignmentHistory::TYPE_ORGANISATION:
                return $this->buildOrganisationConflictMessage($change);
            case AssignmentHistory::TYPE_LIST:
                return $this->buildListConflictMessage($change, $user);
            case AssignmentHistory::TYPE_USER:
                return $this->buildUserConflictMessage($change, $user);
        }

        return '';
    }

    private function buildOrganisationMessage(AssignmentHistory $change): string
    {
        $previousAssignment = $change->getPreviousAssignment();
        $newAssignment = $change->getNewAssignment();

        $message = 'Organisatie: ';
        $message .= 'van <b>' . $this->getOrganisationLabel($previousAssignment) . '</b>';
        $message .= ' naar ';
        $message .= '<b>' . $this->getOrganisationLabel($newAssignment) . '</b>';

        return $message;
    }

    private function buildListMessage(AssignmentHistory $change, EloquentUser $user): string
    {
        $previousAssignment = $change->getPreviousAssignment();
        $newAssignment = $change->getNewAssignment();

        $message = 'Lijst: ';
        $message .= '<b>' . $this->getListLabel($previousAssignment, $user) . '</b>';
        $message .= ' naar ';
        $message .= '<b>' . $this->getListLabel($newAssignment, $user) . '</b>';

        return $message;
    }

    private function buildUserMessage(AssignmentHistory $change, EloquentUser $user): string
    {
        $previousAssignment = $change->getPreviousAssignment();
        $newAssignment = $change->getNewAssignment();

        $message = 'Medewerker: ';
        $message .= '<b>' . $this->getUserLabel($previousAssignment, $user) . '</b>';
        $message .= ' naar ';
        $message .= '<b>' . $this->getUserLabel($newAssignment, $user) . '</b>';

        return $message;
    }

    private function buildOrganisationConflictMessage(AssignmentHistory $change): string
    {
        $newAssignment = $change->getNewAssignment();

        return "Toegewezen aan <b> {$this->getOrganisationLabel($newAssignment)}</b>";
    }

    private function buildListConflictMessage(AssignmentHistory $change, EloquentUser $user): string
    {
        $newAssignment = $change->getNewAssignment();

        return "Verplaatst naar <b> {$this->getListLabel($newAssignment, $user)}</b>";
    }

    private function buildUserConflictMessage(AssignmentHistory $change, EloquentUser $user): string
    {
        $newAssignment = $change->getNewAssignment();

        return "Toegewezen aan <b> {$this->getUserLabel($newAssignment, $user)}</b>";
    }

    private function getListLabel(?CaseAssignmentHistory $assignmentHistory, EloquentUser $user): string
    {
        if ($assignmentHistory === null) {
            return 'geen lijst';
        }

        if ($assignmentHistory->hasList() === false) {
            return 'geen lijst';
        }

        /** @var CaseList|null $list */
        $list = CaseList::withoutGlobalScope(CaseListAuthScope::class)->withTrashed()->find($assignmentHistory->assigned_case_list_uuid);
        if ($list === null) {
            return 'verwijderde lijst';
        }

        if ($user->getOrganisation() === null) {
            throw new RuntimeException('Current user must have an organisation');
        }

        if ($list->organisation_uuid !== $user->getOrganisation()->uuid) {
            /** @var EloquentOrganisation|null $organisation */
            $organisation = EloquentOrganisation::withoutGlobalScope(OrganisationAuthScope::class)->find($list->organisation_uuid);
            if ($organisation === null) {
                return ' -organisatie niet gevonden- ';
            }
            return 'een lijst van ' . $organisation->name ?? ' onbekende organisatie';
        }

        if ($list->is_queue) {
            return 'wachtrij';
        }

        return $assignmentHistory->assigned_case_list_name ?? ' -lijstnaam niet gevonden-';
    }

    private function getUserLabel(?CaseAssignmentHistory $assignmentHistory, EloquentUser $loggedInUser): string
    {
        if ($assignmentHistory === null) {
            return 'geen toewijzing';
        }

        $user = $assignmentHistory->user;
        if ($user === null) {
            return 'geen toewijzing';
        }

        $userOrganisation = $user->getOrganisation();
        if ($userOrganisation === null) {
            throw new RuntimeException('User must have an organisation: ' . $user->uuid);
        }

        $loggedInOrganisation = $loggedInUser->getOrganisation();
        if ($loggedInOrganisation === null) {
            throw new RuntimeException('Current user must have an organisation: ' . $loggedInUser->uuid);
        }

        if ($userOrganisation->uuid !== $loggedInOrganisation->uuid) {
            return 'Een medewerker van ' . $userOrganisation->name;
        }

        return $user->name;
    }

    private function getOrganisationLabel(?CaseAssignmentHistory $previousAssignment): string
    {
        if ($previousAssignment === null) {
            return 'geen toewijzing';
        }

        if ($previousAssignment->organisation === null) {
            return $previousAssignment->case->organisation->name;
        }

        return $previousAssignment->organisation->name;
    }
}
