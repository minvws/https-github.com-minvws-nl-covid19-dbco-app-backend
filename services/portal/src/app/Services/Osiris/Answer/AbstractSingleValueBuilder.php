<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;

/**
 * Base class for builders that return zero or one answer.
 */
abstract class AbstractSingleValueBuilder extends AbstractBuilder
{
    /**
     * Returns the answer value for the given case.
     *
     * Should return null if the answer should not be included in the Osiris response.
     */
    abstract protected function getValue(EloquentCase $case): ?string;

    final public function build(EloquentCase $case): array
    {
        $value = $this->getValue($case);
        if ($value === null) {
            return [];
        }

        return [new Answer($this->getCode(), $value)];
    }
}
