<?php

declare(strict_types=1);

namespace App\Schema\Update;

use function count;

class UpdateDiff
{
    private Update $update;
    private array $fieldDiffs = [];

    public function __construct(Update $update)
    {
        $this->update = $update;
    }

    public function getUpdate(): Update
    {
        return $this->update;
    }

    public function addFieldDiff(UpdateFieldDiff $diff): void
    {
        $this->fieldDiffs[$diff->getField()->getName()] = $diff;
    }

    public function getFieldDiff(string $fieldName): ?UpdateFieldDiff
    {
        return $this->fieldDiffs[$fieldName] ?? null;
    }

    public function getFieldDiffs(): array
    {
        return $this->fieldDiffs;
    }

    public function isEmpty(): bool
    {
        return count($this->fieldDiffs) === 0;
    }
}
