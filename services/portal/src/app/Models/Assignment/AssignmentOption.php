<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;

/**
 * @template T of Assignment
 */
abstract class AssignmentOption extends LeafOption
{
    /** @var T */
    private Assignment $assignment;

    /**
     * @param T $assignment
     */
    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }

    /**
     * @return T
     */
    public function getAssignment(): Assignment
    {
        return $this->assignment;
    }

    abstract protected function isSelectedForCase(EloquentCase $case): bool;

    protected function isEnabledForCase(EloquentCase $case, EloquentOrganisation $selectedOrganisation, bool $validateFull, Cache $cache): bool
    {
        return $this->getAssignment()->isValidForCaseWithSelectedOrganisation($case, $selectedOrganisation, $validateFull, $cache);
    }

    public function updateForCase(EloquentCase $case, EloquentOrganisation $selectedOrganisation, bool $validateFull, Cache $cache): void
    {
        $this->incrementSelected($this->isSelectedForCase($case));
        $this->incrementEnabled($this->isEnabledForCase($case, $selectedOrganisation, $validateFull, $cache));
    }
}
