<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;

/**
 * Represents an assignment.
 */
interface Assignment
{
    /**
     * Checks if this assignment is valid for the user's currently selected organisation,
     * not taking any specific case into account.
     */
    public function isValidForSelectedOrganisation(EloquentOrganisation $selectedOrganisation, bool $validateFull = true, ?Cache $cache = null): bool;

    /**
     * Checks if this assignment is valid for the given case based on the user's currently selected organisation.
     *
     * The `$validateFull` parameter can be set to `false` to optimize for speed. This is mainly used for the
     * assignment options that are already filtered when retrieved from the database.
     */
    public function isValidForCaseWithSelectedOrganisation(EloquentCase $case, EloquentOrganisation $selectedOrganisation, bool $validateFull = true, ?Cache $cache = null): bool;

    /**
     * Apply assignment to case.
     */
    public function applyToCase(EloquentCase $case): void;
}
