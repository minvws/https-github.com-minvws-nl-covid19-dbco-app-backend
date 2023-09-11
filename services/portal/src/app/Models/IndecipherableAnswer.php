<?php

declare(strict_types=1);

namespace App\Models;

/**
 * An answer that cannot be read anymore because the encryption key has expired.
 */
class IndecipherableAnswer extends Answer
{
    public const INDECIPHERABLE = '_INDECIPHERABLE_';

    /**
     * No value.
     */
    public function toFormValue(): array
    {
        return ['value' => self::INDECIPHERABLE];
    }

    public function fromFormValue(array $formData): void
    {
    }
}
