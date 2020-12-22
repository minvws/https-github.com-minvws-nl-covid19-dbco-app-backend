<?php

namespace App\Repositories;

interface StateRepository
{
    /**
     * Get the copied fields for a case, or for a cases' task
     * @param string $caseUuid
     * @param string $taskUuid Set to null for getting the case level fields
     * @return array
     */
    public function getCopiedFields(string $caseUuid, ?string $taskUuid): array;

    /**
     * Mark a field as copied
     *
     * @param string $caseUuid
     * @param string $taskUuid Set to null if you want to mark a case level field.
     * @param string $fieldName
     * @return bool Is this the first time something for this
     * task is copied? (can be used to mark a copied-at marker at db level
     */
    public function markFieldAsCopied(string $caseUuid, ?string $taskUuid, string $fieldName): bool;

    public function clearCopiedFields(string $caseUuid, ?string $taskUuid);
}
