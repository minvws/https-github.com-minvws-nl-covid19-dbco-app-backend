<?php

namespace App\Repositories;

class SessionStateRepository implements StateRepository
{
    public function markFieldAsCopied(string $caseUuid, ?string $taskUuid, string $fieldName): bool
    {
        $isFirst = false;

        $cacheKey = $caseUuid;
        if ($taskUuid != null) {
            $cacheKey .= '_'.$taskUuid;
        }

        $storedFields = session('storedFields', []);
        if (!isset($storedFields[$cacheKey])) {
            $storedFields[$cacheKey] = [];
        }

        if (empty($storedFields[$cacheKey])) {
            $isFirst = true;
        }

        if (!in_array($fieldName, $storedFields[$cacheKey])) {
            $storedFields[$cacheKey][] = $fieldName;
        }

        session(['storedFields' => $storedFields]);

        return $isFirst;
    }

    public function getCopiedFields(string $caseUuid, ?string $taskUuid): array
    {
        $cacheKey = $caseUuid;
        if ($taskUuid != null) {
            $cacheKey .= '_'.$taskUuid;
        }
        $storedFields = session('storedFields') ?? [];
        return $storedFields[$cacheKey] ?? [];
    }

    public function clearCopiedFields(string $caseUuid, ?string $taskUuid)
    {
        $cacheKey = $caseUuid;
        if ($taskUuid != null) {
            $cacheKey .= '_'.$taskUuid;
        }

        $storedFields = session('storedFields', []);
        $storedFields[$cacheKey] = [];
        session(['storedFields' => $storedFields]);
    }
}
