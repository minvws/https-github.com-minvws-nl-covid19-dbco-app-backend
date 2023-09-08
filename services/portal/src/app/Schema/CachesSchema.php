<?php

declare(strict_types=1);

namespace App\Schema;

/**
 * Makes sure the schema for this object is only instantiated once.
 *
 * Subclasses can override the loadSchema method to setup the schema.
 */
trait CachesSchema
{
    /**
     * Schema for this object.
     *
     * @return Schema<static>
     */
    final public static function getSchema(): Schema
    {
        return SchemaCache::get(static::class, static function () {
            $schema = static::loadSchema();
            static::postLoadSchema($schema);
            return $schema;
        });
    }

    /**
     * Load schema.
     *
     * @return Schema<static>
     */
    protected static function loadSchema(): Schema
    {
        return new Schema(static::class);
    }

    /**
     * Called after the loadSchema method has been called. Can be used by classes to implement extra logic
     * after loading the schema defined in a subclass. This method will only be called once after which the
     * schema will be cached.
     */
    protected static function postLoadSchema(Schema $schema): void
    {
    }
}
