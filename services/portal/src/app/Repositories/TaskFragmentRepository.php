<?php

declare(strict_types=1);

namespace App\Repositories;

use Exception;

interface TaskFragmentRepository
{
    /**
     * Returns the fragments with the given names for the given task.
     *
     * @param string $taskUuid Task ID.
     * @param array $fragmentNames Fragment names.
     * @param bool $includingSoftDeletes Also fetch fragments from soft deleted task.
     *
     * @return array Fragments indexed by name.
     *
     * @throws Exception
     */
    public function loadTaskFragments(string $taskUuid, array $fragmentNames, bool $includingSoftDeletes = false): array;

    /**
     * Stores the given fragments for the given task.
     *
     * @param string $taskUuid Task ID.
     * @param array $fragments Fragments indexed by name.
     *
     * @throws Exception
     */
    public function storeTaskFragments(string $taskUuid, array $fragments): void;
}
