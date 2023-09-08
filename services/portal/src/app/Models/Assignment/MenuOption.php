<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use function array_filter;
use function count;

abstract class MenuOption extends SelectableOption
{
    /** @var array<SelectableOption> $options */
    protected array $options = [];

    public function addChildOption(SelectableOption $option): SelectableOption
    {
        $this->options[] = $option;
        return $option;
    }

    public function getChildOptions(): array
    {
        return $this->options;
    }

    public function isChildOptionSelected(): bool
    {
        foreach ($this->options as $option) {
            if ($option->isSelected()) {
                return true;
            }
        }

        return false;
    }

    public function isAvailable(): bool
    {
        return parent::isAvailable() && count(array_filter($this->options, static fn ($o) => $o->isAvailable() || $o->isSelected())) > 0;
    }
}
