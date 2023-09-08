<?php

declare(strict_types=1);

namespace App\Schema;

use function call_user_func;
use function get_parent_class;
use function preg_match;

class SchemaCache
{
    private static array $schemas = [];

    /**
     * Returns the (possibly) cached schema for the given class or load the schema
     * using the given callback.
     *
     * @template T of SchemaObject
     *
     * @param class-string<T> $class Schema object class.
     * @param callable $load Schema loader.
     *
     * @return Schema<T>
     */
    public static function get(string $class, callable $load): Schema
    {
        // if called on a version subclass make sure we cache/use the base class schema
        while ($class && preg_match('/V[1-9][0-9]*$/', $class)) {
            $class = get_parent_class($class);
        }

        if (!isset(self::$schemas[$class])) {
            self::$schemas[$class] = call_user_func($load);
        }

        return self::$schemas[$class];
    }

    public static function clear(): void
    {
        self::$schemas = [];
    }
}
