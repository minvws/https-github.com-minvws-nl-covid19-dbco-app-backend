<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use RuntimeException;

use function is_bool;

class Environment
{
    public static function isDevelopment(): bool
    {
        return self::isEnvironment(['dev', 'development', 'local']);
    }

    public static function isProduction(): bool
    {
        return self::isEnvironment(['production']);
    }

    public static function isTesting(): bool
    {
        return self::isEnvironment(['test', 'testing']);
    }

    public static function isDevelopmentOrTesting(): bool
    {
        return self::isDevelopment() || self::isTesting();
    }

    private static function isEnvironment(array $environmentNames): bool
    {
        $environment = App::environment($environmentNames);

        if (!is_bool($environment)) {
            throw new RuntimeException('Unable to determine environment');
        }

        return $environment;
    }
}
