<?php

declare(strict_types=1);

namespace App\Repositories;

use Exception;

interface ContextFragmentRepository
{
    /**
     * Returns the fragments with the given names for the given context.
     *
     * @param string $contextUuid Context ID.
     * @param array $fragmentNames Fragment names.
     *
     * @return array Fragments indexed by name.
     *
     * @throws Exception
     */
    public function loadContextFragments(string $contextUuid, array $fragmentNames): array;

    /**
     * Stores the given fragments for the given context.
     *
     * @param string $contextUuid Context ID.
     * @param array $fragments Fragments indexed by name.
     *
     * @throws Exception
     */
    public function storeContextFragments(string $contextUuid, array $fragments): void;
}
