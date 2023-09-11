<?php

declare(strict_types=1);

namespace Tests\Helpers;

use function config;
use function sprintf;

class ConfigHelper
{
    public static function get(string $key): mixed
    {
        if (!config()->has($key)) {
            throw new ConfigNotFoundException(
                sprintf('Config option is not known: %s', $key),
            );
        }

        return config()->get($key);
    }

    public static function set(string $key, mixed $value): void
    {
        if (!config()->has($key)) {
            throw new ConfigNotFoundException(
                sprintf('Config option is not known: %s', $key),
            );
        }

        config()->set($key, $value);
    }

    public static function setFeatureFlag(
        string $featureFlagConfig,
        bool $enable,
        string $configFile = 'featureflag',
    ): void {
        $featureFlagConfig = sprintf('%s.%s', $configFile, $featureFlagConfig);

        self::set($featureFlagConfig, $enable);
    }

    public static function enableFeatureFlag(string $featureFlagConfig, string $configfile = 'featureflag'): void
    {
        self::setFeatureFlag($featureFlagConfig, true, $configfile);
    }

    public static function disableFeatureFlag(string $featureFlagConfig, string $configfile = 'featureflag'): void
    {
        self::setFeatureFlag($featureFlagConfig, false, $configfile);
    }
}
