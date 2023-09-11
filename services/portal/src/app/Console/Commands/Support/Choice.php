<?php

declare(strict_types=1);

namespace App\Console\Commands\Support;

/**
 * @template T
 */
class Choice
{
    /**
     * @param T $value
     */
    public function __construct(public readonly string $label, public readonly mixed $value, public bool $selected = false)
    {
    }
}
