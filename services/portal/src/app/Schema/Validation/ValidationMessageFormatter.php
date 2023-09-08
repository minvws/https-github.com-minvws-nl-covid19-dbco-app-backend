<?php

declare(strict_types=1);

namespace App\Schema\Validation;

use function array_map;
use function is_array;
use function is_string;
use function sprintf;
use function str_replace;
use function str_starts_with;

final class ValidationMessageFormatter
{
    public static function prefixLabel(string $prefix, array|string $translations): array|string
    {
        if (is_array($translations)) {
            return array_map(
                static fn ($translations) => self::prefixLabel($prefix, $translations),
                $translations,
            );
        }

        if (!is_string($translations) || !str_starts_with($translations, ':Attribute')) {
            return $translations;
        }

        return str_replace(':Attribute', sprintf('%s ":Attribute"', $prefix), $translations);
    }
}
