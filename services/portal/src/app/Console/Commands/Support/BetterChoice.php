<?php

declare(strict_types=1);

namespace App\Console\Commands\Support;

use function array_key_exists;
use function assert;
use function in_array;
use function is_array;
use function is_string;

trait BetterChoice
{
    /**
     * @template T
     *
     * @param non-empty-array<int|string, Choice<T>|Choice<null>> $choices
     *
     * @return array<T|null>
     */
    private function betterChoiceBase(string $question, array $choices, bool $multiple): array
    {
        $options = [];
        $default = null;

        foreach ($choices as $key => $choice) {
            $options[$key] = $choice->label;
            if ($choice->selected) {
                $default .= ($default !== null ? ',' : '') . $key;
            }
        }

        $selected = $this->choice($question, $options, default: $default, multiple: $multiple);

        $values = [];
        foreach ($choices as $choice) {
            if (
                (
                    is_string($selected)
                    && $choice->label === $selected
                )
                ||
                (
                    is_array($selected)
                    && in_array($choice->label, $selected, true)
                )
            ) {
                $values[] = $choice->value;
            }
        }

        return $values;
    }

    /**
     * @template T
     *
     * @param non-empty-array<int|string, Choice<T>|Choice<null>> $choices
     *
     * @return T|null
     */
    protected function betterSingleChoice(string $question, array $choices): mixed
    {
        $values = $this->betterChoiceBase($question, $choices, false);
        assert(array_key_exists(0, $values));
        return $values[0];
    }

    /**
     * @template T
     *
     * @param non-empty-array<int|string, Choice<T>|Choice<null>> $choices
     *
     * @return array<T|null>
     */
    protected function betterMultipleChoice(string $question, array $choices): mixed
    {
        return $this->betterChoiceBase($question, $choices, true);
    }
}
