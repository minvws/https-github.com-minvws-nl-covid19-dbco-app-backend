<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;

use function array_map;

/**
 * Base class for builders that return zero or multiple answers with the same code.
 */
abstract class AbstractMultiValueBuilder extends AbstractBuilder
{
    /**
     * Returns the answer values for the given case.
     *
     * Should return an empty array if the answer should not be included in the Osiris response.
     *
     * @return array<string>
     */
    abstract protected function getValues(EloquentCase $case): array;

    final public function build(EloquentCase $case): array
    {
        return array_map(fn (string $value) => new Answer($this->getCode(), $value), $this->getValues($case));
    }
}
