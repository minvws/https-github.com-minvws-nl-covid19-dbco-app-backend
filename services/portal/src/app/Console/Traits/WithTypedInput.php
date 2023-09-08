<?php

declare(strict_types=1);

namespace App\Console\Traits;

use Illuminate\Console\Concerns\InteractsWithIO;

use function filter_var;
use function is_array;
use function is_numeric;
use function is_string;

use const FILTER_VALIDATE_BOOLEAN;

trait WithTypedInput
{
    use InteractsWithIO;

    public function getStringArgument(string $name, string $default = ''): string
    {
        $argValue = $this->argument($name);

        return is_string($argValue) ? $argValue : $default;
    }

    public function getIntegerArgument(string $name, int $default = 0): int
    {
        $argValue = $this->argument($name);

        return is_numeric($argValue) ? (int) $argValue : $default;
    }

    public function getBooleanArgument(string $name): bool
    {
        return filter_var($this->argument($name), FILTER_VALIDATE_BOOLEAN);
    }

    public function getStringOption(string $name, string $default = ''): string
    {
        $optionValue = $this->option($name);

        return is_string($optionValue) ? $optionValue : $default;
    }

    public function getIntegerOption(string $name, int $default = 0): int
    {
        $optionValue = $this->option($name);

        return is_numeric($optionValue) ? (int) $optionValue : $default;
    }

    public function getBooleanOption(string $name): bool
    {
        return filter_var($this->option($name), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param array<int,string> $default
     *
     * @return array<int,string>
     */
    public function getArrayOption(string $name, array $default = []): array
    {
        $optionValue = $this->option($name);

        return is_array($optionValue) ? $optionValue : $default;
    }
}
