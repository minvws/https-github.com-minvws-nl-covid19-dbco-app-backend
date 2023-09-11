<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use function assert;
use function str_ends_with;
use function strrpos;
use function substr;

/**
 * Base class for builders that return zero or one answer.
 */
abstract class AbstractBuilder implements Builder
{
    /**
     * Returns the Osiris code for this answer.
     *
     * The default implementation assumes the builder class is named <OsirisCode>Builder and
     * extracts the Osiris code from it.
     */
    protected function getCode(): string
    {
        $code = static::class;

        // strip namespace
        $p = strrpos($code, '\\');
        assert($p !== false);
        $code = substr($code, $p + 1);

        // strip builder part
        assert(str_ends_with($code, 'Builder'));
        return substr($code, 0, -7);
    }
}
