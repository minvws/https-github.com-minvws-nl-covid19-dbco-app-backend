<?php

declare(strict_types=1);

namespace App\Models\Assignment;

abstract class SelectableOption extends Option
{
    private int $selectedCount = 0;
    private int $enabledCount = 0;
    private int $disabledCount = 0;

    public function isSelected(): bool
    {
        return $this->selectedCount > 0;
    }

    public function incrementSelected(bool $selected): void
    {
        $this->selectedCount += $selected ? 1 : 0;
    }

    public function isEnabled(): bool
    {
        return $this->disabledCount === 0;
    }

    public function incrementEnabled(bool $enabled): void
    {
        $this->enabledCount += $enabled ? 1 : 0;
        $this->disabledCount += $enabled ? 0 : 1;
    }

    public function isAvailable(): bool
    {
        return $this->enabledCount > 0;
    }
}
