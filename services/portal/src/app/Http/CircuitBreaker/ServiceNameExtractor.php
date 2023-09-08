<?php

declare(strict_types=1);

namespace App\Http\CircuitBreaker;

use App\Http\CircuitBreaker\Exceptions\ServiceNameNotConfiguredException;

use function array_key_exists;

final class ServiceNameExtractor
{
    private const OPTION_KEY = 'service_name';

    public function extract(array $options): string
    {
        if (array_key_exists(self::OPTION_KEY, $options)) {
            return $options[self::OPTION_KEY];
        }

        throw ServiceNameNotConfiguredException::missingOption(self::OPTION_KEY);
    }
}
