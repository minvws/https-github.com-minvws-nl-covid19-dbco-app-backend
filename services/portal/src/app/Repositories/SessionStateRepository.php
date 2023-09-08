<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Session\Store;

use function array_key_exists;
use function count;
use function in_array;
use function is_array;

class SessionStateRepository implements StateRepository
{
    public function __construct(
        private readonly Store $session,
    ) {
    }

    public function markFieldAsCopied(string $caseUuid, ?string $taskUuid, string $fieldName): bool
    {
        $isFirst = false;
        $cacheKey = $this->getCacheKey($caseUuid, $taskUuid);

        $storedFields = $this->getStoredFieldsFromSession();
        if (!array_key_exists($cacheKey, $storedFields)) {
            $storedFields[$cacheKey] = [];
        }

        if (count($storedFields[$cacheKey]) === 0) {
            $isFirst = true;
        }

        if (!in_array($fieldName, $storedFields[$cacheKey], true)) {
            $storedFields[$cacheKey][] = $fieldName;
        }

        $this->saveStoredFieldsInSession($storedFields);

        return $isFirst;
    }

    /**
     * @return array<string>
     */
    public function getCopiedFields(string $caseUuid, ?string $taskUuid): array
    {
        $storedFields = $this->getStoredFieldsFromSession();
        $cacheKey = $this->getCacheKey($caseUuid, $taskUuid);

        return $storedFields[$cacheKey] ?? [];
    }

    public function clearCopiedFields(string $caseUuid, ?string $taskUuid): void
    {
        $cacheKey = $this->getCacheKey($caseUuid, $taskUuid);

        $storedFields = $this->getStoredFieldsFromSession();
        $storedFields[$cacheKey] = [];

        $this->saveStoredFieldsInSession($storedFields);
    }

    private function getCacheKey(string $caseUuid, ?string $taskUuid): string
    {
        $cacheKey = $caseUuid;

        if ($taskUuid !== null) {
            $cacheKey .= '_' . $taskUuid;
        }

        return $cacheKey;
    }

    /**
     * @return array<string, array<string>>
     */
    private function getStoredFieldsFromSession(): array
    {
        $storedFields = $this->session->get('storedFields', []);

        if (!is_array($storedFields)) {
            return [];
        }

        return $storedFields;
    }

    /**
     * @param array<string, array<string>> $storedFields
     */
    private function saveStoredFieldsInSession(array $storedFields): void
    {
        $this->session->put('storedFields', $storedFields);
    }
}
