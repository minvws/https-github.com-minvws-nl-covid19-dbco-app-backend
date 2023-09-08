<?php

declare(strict_types=1);

namespace App\Models\Eloquent\Traits;

use Illuminate\Support\Str;

use function array_key_exists;
use function method_exists;

/**
 * Camel case support for Eloquent model attributes.
 *
 * Only compatible with Eloquent model subclasses.
 */
trait CamelCaseAttributes
{
    protected static array $mappingCache = [];

    protected function getAttributeNameForKey(string $key): string
    {
        return Str::snake($key);
    }

    /**
     * @inheritdoc
     *
     * @param string $key
     */
    public function getAttribute($key): mixed
    {
        if (isset(static::$mappingCache[$key])) {
            return parent::getAttribute(static::$mappingCache[$key]);
        }

        // First check if a relation with the exact name exists.
        if (array_key_exists($key, $this->relations) || method_exists($this, $key)) {
            static::$mappingCache[$key] = $key;
            return parent::getAttribute($key);
        }

        // Try snake case.
        static::$mappingCache[$key] = $this->getAttributeNameForKey($key);
        return parent::getAttribute(static::$mappingCache[$key]);
    }

    /**
     * @inheritdoc
     *
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute($key, $value): mixed
    {
        if (isset(static::$mappingCache[$key])) {
            return parent::setAttribute(static::$mappingCache[$key], $value);
        }

        // First check if a relation with the exact name exists.
        if (array_key_exists($key, $this->relations) || method_exists($this, $key)) {
            static::$mappingCache[$key] = $key;
            return parent::setAttribute($key, $value);
        }

        // Try snake case.
        static::$mappingCache[$key] = $this->getAttributeNameForKey($key);
        return parent::setAttribute(static::$mappingCache[$key], $value);
    }
}
