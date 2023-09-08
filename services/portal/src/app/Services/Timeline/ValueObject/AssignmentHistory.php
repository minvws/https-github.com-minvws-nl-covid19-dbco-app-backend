<?php

declare(strict_types=1);

namespace App\Services\Timeline\ValueObject;

use App\Models\Eloquent\CaseAssignmentHistory;

class AssignmentHistory
{
    public const TYPE_USER = 'user';
    public const TYPE_ORGANISATION = 'organisation';
    public const TYPE_LIST = 'list';

    private string $type;
    private CaseAssignmentHistory $newAssignment;
    private ?CaseAssignmentHistory $previousAssignment;

    public function __construct(string $type, CaseAssignmentHistory $newValue, ?CaseAssignmentHistory $previousValue)
    {
        $this->type = $type;
        $this->newAssignment = $newValue;
        $this->previousAssignment = $previousValue;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getNewAssignment(): CaseAssignmentHistory
    {
        return $this->newAssignment;
    }

    public function getPreviousAssignment(): ?CaseAssignmentHistory
    {
        return $this->previousAssignment;
    }
}
