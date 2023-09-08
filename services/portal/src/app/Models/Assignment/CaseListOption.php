<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use App\Models\Eloquent\CaseList;
use App\Models\Eloquent\EloquentCase;
use MinVWS\Codable\EncodingContainer;

/**
 * @extends AssignmentOption<CaseListAssignment>
 */
class CaseListOption extends AssignmentOption
{
    public function __construct(CaseListAssignment $assignment)
    {
        parent::__construct($assignment);
    }

    public function getCaseList(): CaseList
    {
        return $this->getAssignment()->getCaseList();
    }

    public function getLabel(): string
    {
        $label = $this->getCaseList()->name;

        if ($this->getCaseList()->is_queue && !$this->getCaseList()->is_default) {
            $label .= ' (wachtrij)';
        }

        return $label;
    }

    public function encode(EncodingContainer $container): void
    {
        parent::encode($container);

        $container->isQueue = $this->getCaseList()->is_queue;
        $container->assignmentType = 'caseList';
        $container->assignment->assignedCaseListUuid = $this->isSelected() ? null : $this->getCaseList()->uuid;
    }

    protected function isSelectedForCase(EloquentCase $case): bool
    {
        return $case->assigned_case_list_uuid === $this->getCaseList()->uuid;
    }
}
