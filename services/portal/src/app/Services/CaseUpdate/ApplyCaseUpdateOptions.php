<?php

declare(strict_types=1);

namespace App\Services\CaseUpdate;

use function array_keys;
use function count;
use function explode;

class ApplyCaseUpdateOptions
{
    private array $caseFragmentFields;
    private array $contactsFragmentFields;

    private function __construct(array $caseFragmentFields, array $contactsFragmentFields)
    {
        $this->caseFragmentFields = $caseFragmentFields;
        $this->contactsFragmentFields = $contactsFragmentFields;
    }

    public function hasCaseFragments(): bool
    {
        return count($this->caseFragmentFields) > 0;
    }

    public function getCaseFragmentNames(): array
    {
        return array_keys($this->caseFragmentFields);
    }

    public function getCaseFragmentFields(string $fragmentName): array
    {
        return $this->caseFragmentFields[$fragmentName] ?? [];
    }

    public function hasContacts(): bool
    {
        return count($this->contactsFragmentFields) > 0;
    }

    public function getContactUuids(): array
    {
        return array_keys($this->contactsFragmentFields);
    }

    public function getContactFragmentNames(string $uuid): array
    {
        return array_keys($this->contactsFragmentFields[$uuid] ?? []);
    }

    public function getContactFragmentFields(string $uuid, string $fragmentName): array
    {
        return $this->contactsFragmentFields[$uuid][$fragmentName] ?? [];
    }

    /**
     * @param array<string> $fieldIds
     */
    public static function forFieldIds(array $fieldIds): self
    {
        $caseFragmentFields = [];
        $contactsFragmentFields = [];
        foreach ($fieldIds as $fieldId) {
            $parts = explode('.', $fieldId);
            if (count($parts) === 2) {
                // case fragment field
                $caseFragmentFields[$parts[0]][] = $parts[1];
            } elseif (count($parts) === 4) {
                // contact fragment field
                $contactsFragmentFields[$parts[1]][$parts[2]][] = $parts[3];
            }
        }

        return new self($caseFragmentFields, $contactsFragmentFields);
    }
}
