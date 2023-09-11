<?php

declare(strict_types=1);

namespace App\Repositories;

use Exception;

interface CaseFragmentRepository
{
    // phpcs:disable Generic.Commenting.Todo.TaskFound -- baseline
    /**
     * Returns the fragments with the given names for the given case.
     *
     * @param string $caseUuid Case ID.
     * @param array $fragmentNames Fragment names.
     * @param bool $includingSoftDeletes Also fetch fragments from soft deleted case.
     * @param bool $disableAuthFilter Disable the global auth filter. TODO: This is a temporary solution until we can access the fragments directly on the case object.
     *
     * @return array Fragments indexed by name.
     *
     * @throws Exception
     */
    // phpcs:enable Generic.Commenting.Todo.TaskFound -- baseline
    public function loadCaseFragments(string $caseUuid, array $fragmentNames, bool $includingSoftDeletes = false, bool $disableAuthFilter = false): array;

    /**
     * Stores the given fragments for the given case.
     *
     * @param string $caseUuid Case ID.
     * @param array $fragments Fragments indexed by name.
     *
     * @throws Exception
     */
    public function storeCaseFragments(string $caseUuid, array $fragments): void;
}
