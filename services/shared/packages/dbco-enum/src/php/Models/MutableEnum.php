<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

use MinVWS\DBCO\Enum\Models\Enum;
use stdClass;

class MutableEnum extends Enum
{
    private static ?object $schema = null;

    public static function resetEnumSchema(): void
    {
        static::$schema = null;
    }

    public static function setEnumSchema(object $schema): void
    {
        static::$schema = $schema;
    }

    protected static function enumSchema(): object
    {
        return static::$schema ?? new stdClass();
    }
}
