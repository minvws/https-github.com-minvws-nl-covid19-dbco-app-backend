<?php

declare(strict_types=1);

namespace App\Models\Assignment;

use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

use function array_filter;
use function array_values;
use function count;

class Options implements Encodable
{
    /** @var array<Option> $options */
    private array $options = [];

    /**
     * @template T of Option
     *
     * @param T $option
     *
     * @return T
     */
    public function addOption(Option $option): Option
    {
        $this->options[] = $option;
        return $option;
    }

    public function encode(EncodingContainer $container): void
    {
        $container->options = $this->getRootOptions();
    }

    /**
     * Returns the root assignment options.
     *
     * @return array<Option>
     */
    public function getRootOptions(): array
    {
        $options = [];
        $hasUserOption = false;

        foreach ($this->options as $option) {
            if ($option instanceof UnassignedOption && $option->isAvailable()) {
                $options[] = $option;
            } elseif ($option instanceof DefaultCaseQueueOption && $option->isAvailable()) {
                $caseListMenu = array_values(array_filter($this->options, static fn ($o) => $o instanceof CaseListMenuOption))[0] ?? null;
                if (
                    !$caseListMenu ||
                    !$caseListMenu->isChildOptionSelected() ||
                    $caseListMenu->getChildOptions()[0]->isSelected()
                ) {
                    $options[] = $option;
                }
            } elseif ($option instanceof ReturnToOwnerOption && $option->isAvailable()) {
                $options[] = $option;
            } elseif ($option instanceof MenuOption && $option->isAvailable()) {
                $options[] = $option;
            } elseif ($option instanceof UserOption && $option->isAvailable()) {
                if (count($options) && !$hasUserOption) {
                    $options[] = new SeparatorOption();
                }
                $hasUserOption = true;
                $options[] = $option;
            }
        }

        return $options;
    }

    /**
     * Recursively returns all selectable options.
     *
     * @return array<SelectableOption>
     */
    public function getSelectableOptions(): array
    {
        $options = [];
        foreach ($this->options as $option) {
            if ($option instanceof SelectableOption) {
                $options[] = $option;
            }

            if (!($option instanceof MenuOption)) {
                continue;
            }

            foreach ($option->getChildOptions() as $childOption) {
                $options[] = $childOption;
            }
        }

        return $options;
    }
}
