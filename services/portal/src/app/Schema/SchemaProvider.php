<?php

declare(strict_types=1);

namespace App\Schema;

/**
 * Provides a schema.
 *
 * @template T of SchemaObject
 */
interface SchemaProvider
{
    /**
     * Returns a schema.
     *
     * @return Schema<T>
     */
    public static function getSchema(): Schema;
}
