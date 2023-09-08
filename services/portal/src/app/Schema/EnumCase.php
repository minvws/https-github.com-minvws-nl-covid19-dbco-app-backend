<?php

declare(strict_types=1);

namespace App\Schema;

use function assert;
use function is_string;
use function json_encode;

class EnumCase
{
    private static array $cache = [];

    public static function create(string|int $value, ?string $name = null): self
    {
        $key = json_encode([$value, $name]);
        assert(is_string($key));

        if (!isset(self::$cache[$key])) {
            self::$cache[$key] = new EnumCase($value, $name);
        }

        return self::$cache[$key];
    }

    protected function __construct(public readonly string|int $value, public readonly ?string $name = null)
    {
    }
}
