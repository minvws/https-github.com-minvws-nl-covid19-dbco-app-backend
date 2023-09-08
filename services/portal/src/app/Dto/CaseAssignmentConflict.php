<?php

declare(strict_types=1);

namespace App\Dto;

use Illuminate\Contracts\Support\Arrayable;

class CaseAssignmentConflict implements Arrayable
{
    private string $caseId;
    private string $assignmentStatus;

    public function __construct(
        string $caseId,
        string $assignmentStatus,
    ) {
        $this->caseId = $caseId;
        $this->assignmentStatus = $assignmentStatus;
    }

    public function getcaseId(): string
    {
        return $this->caseId;
    }

    public function getAssignmentStatus(): string
    {
        return $this->assignmentStatus;
    }

    public function toArray(): array
    {
        return [
            'caseId' => $this->caseId,
            'assignmentStatus' => $this->assignmentStatus,
        ];
    }
}
