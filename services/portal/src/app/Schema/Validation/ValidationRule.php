<?php

declare(strict_types=1);

namespace App\Schema\Validation;

class ValidationRule
{
    public const TAG_ALWAYS = 'tag_always';

    public const TAG_OSIRIS_INITIAL = 'tag_osiris_initial';
    public const TAG_OSIRIS_FINAL = 'tag_osiris_final';

    private function __construct(
        public mixed $rule,
        public readonly array $tags = [],
    ) {
    }

    /**
     * @param mixed $rule A value that is compatible with a rule within Laravel Validation
     */
    public static function create(mixed $rule, array $tags = []): ValidationRule
    {
        return new ValidationRule($rule, $tags);
    }
}
