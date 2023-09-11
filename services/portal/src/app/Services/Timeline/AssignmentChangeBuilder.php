<?php

declare(strict_types=1);

namespace App\Services\Timeline;

use App\Models\Eloquent\CaseAssignmentHistory;
use App\Services\Timeline\ValueObject\AssignmentHistory;

class AssignmentChangeBuilder
{
    /**
     * @return array<AssignmentHistory>
     */
    public function getAssignmentChanges(
        CaseAssignmentHistory $newAssignment,
        ?CaseAssignmentHistory $previousAssignment,
    ): array {
        $result = [];

        $fields = [
            AssignmentHistory::TYPE_USER => 'assigned_user_uuid',
            AssignmentHistory::TYPE_LIST => 'assigned_case_list_uuid',
            AssignmentHistory::TYPE_ORGANISATION => 'assigned_organisation_uuid',
        ];

        foreach ($fields as $type => $fieldName) {
            $change = $this->getChange($type, $fieldName, $newAssignment, $previousAssignment);
            if ($change !== null) {
                $result[$change->getType()] = $change;
            }
        }

        return $result;
    }

    private function getChange(
        string $type,
        string $field,
        CaseAssignmentHistory $newAssignment,
        ?CaseAssignmentHistory $previousAssignment,
    ): ?AssignmentHistory {
        if ($previousAssignment === null && $newAssignment->{$field} !== null) {
            return new AssignmentHistory($type, $newAssignment, null);
        }

        if ($previousAssignment !== null && $newAssignment->{$field} !== $previousAssignment->{$field}) {
            return new AssignmentHistory($type, $newAssignment, $previousAssignment);
        }

        return null;
    }
}
