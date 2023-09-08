<?php

declare(strict_types=1);

namespace App\Schema\Traits;

use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use RuntimeException;

use function call_user_func;
use function is_a;

/**
 * Trait that can be used to expose a factory method for creating instances of this
 * class with a certain schema version.
 *
 * If the (sub-)class doesn't implement the SchemaProvider interface an exception will be thrown at run-time.
 *
 * @implements SchemaObject<static>
 * @implements SchemaProvider<static>
 */
trait NewInstanceWithVersion
{
    /**
     * Create a new instance of this class for the given schema version.
     *
     * @param int $version Schema version number.
     * @param callable|null $initializer Callback that can be used to initialize some properties for thew new instance. Will be based the new instance as first argument.
     *
     * @return static
     */
    final public static function newInstanceWithVersion(int $version, ?callable $initializer = null): SchemaObject
    {
        $class = static::class;
        if (!is_a($class, SchemaProvider::class, true)) {
            throw new RuntimeException(__METHOD__ . ' can only be called for subclasses that implement' . SchemaProvider::class);
        }

        $instance = $class::getSchema()->getVersion($version)->newInstance();
        if ($initializer !== null) {
            call_user_func($initializer, $instance);
        }
        return $instance;
    }
}
