<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use MinVWS\Codable\EncodingContainer;

class NullCaseListOption extends LeafOption
{
    public function getAssignment(): NullCaseListAssignment
    {
        return new NullCaseListAssignment();
    }

    public function updateForCase(
        EloquentCase $case,
        EloquentOrganisation $selectedOrganisation,
        bool $validateFull,
        Cache $cache,
    ): void {
        $this->incrementSelected($this->isSelectedForCase($case));
        $this->incrementEnabled($this->isEnabledForCase($case, $selectedOrganisation, $validateFull, $cache));
    }

    public function getLabel(): string
    {
        return 'Geen lijst';
    }

    public function encode(EncodingContainer $container): void
    {
        $container->type = 'option';
        $container->label = $this->getLabel();
        $container->isSelected = $this->isSelected();
        $container->isEnabled = $this->isEnabled();
        $container->isQueue = null;
        $container->assignmentType = 'caseList';
        $container->assignment->assignedCaseListUuid = null;
    }

    private function isSelectedForCase(EloquentCase $case): bool
    {
        return $case->assigned_case_list_uuid === null;
    }

    private function isEnabledForCase(
        EloquentCase $case,
        EloquentOrganisation $selectedOrganisation,
        bool $validateFull,
        Cache $cache,
    ): bool {
        return $this->getAssignment()->isValidForCaseWithSelectedOrganisation($case, $selectedOrganisation, $validateFull, $cache);
    }
}
