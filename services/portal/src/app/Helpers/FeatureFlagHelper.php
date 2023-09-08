<?php

declare(strict_types=1);

namespace App\Helpers;

use function config;
use function sprintf;

class FeatureFlagHelper
{
    public static function isEnabled(string ...$names): bool
    {
        foreach ($names as $name) {
            if (config(sprintf('featureflag.%s', $name), true) === false) {
                return false;
            }
        }

        return true;
    }

    public static function isDisabled(string ...$names): bool
    {
        return !self::isEnabled(...$names);
    }
}
