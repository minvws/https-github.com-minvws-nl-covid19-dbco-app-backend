<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Exceptions\PostalCodeValidationException;
use Axlon\PostalCodeValidation\Support\Facades\PostalCodes;
use Illuminate\Support\Str;

class PostalCodeHelper
{
    /**
     * @throws PostalCodeValidationException
     */
    public static function validate(string $postalCode, string $countryCode = 'NL'): void
    {
        if (!PostalCodes::passes($countryCode, $postalCode)) {
            throw new PostalCodeValidationException('invalid postal code');
        }
    }

    public static function normalize(string $postalCode): string
    {
        return Str::upper(Str::remove(' ', $postalCode));
    }

    /**
     * @throws PostalCodeValidationException
     */
    public static function normalizeAndValidate(string $postalCode, string $countryCode = 'NL'): string
    {
        $postalCode = self::normalize($postalCode);

        self::validate($postalCode, $countryCode);

        return $postalCode;
    }
}
