<?php


namespace App\Models;

/**
 * An answer that cannot be read anymore because the encryption key has expired.
 *
 * @package App\Models
 */
class IndecipherableAnswer extends Answer
{
    public const INDECIPHERABLE = '_INDECIPHERABLE_';

    /**
     * We assume the answer is completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return true;
    }

    /**
     * No value.
     */
    public function toFormValue(): array
    {
        return ['value' => self::INDECIPHERABLE];
    }

    public function fromFormValue(array $formData): void {}
}
