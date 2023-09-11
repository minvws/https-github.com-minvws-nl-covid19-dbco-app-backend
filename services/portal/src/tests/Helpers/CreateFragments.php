<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Models\Eloquent\EloquentCase;
use App\Schema\FragmentModel;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\SchemaVersion;

use function call_user_func;

trait CreateFragments
{
    use QuerySchema;

    /**
     * @param callable(T):void $initializer
     * @param string $field Can be a dot separated string to get a nested field
     */
    private function createLatestEloquentCaseFragmentInstance(
        string $field,
        ?callable $initializer = null,
    ): SchemaObject {
        $schemaVersion = (new EloquentCase())->getSchemaVersion();

        return $this->createFragmentInstance($schemaVersion, $field, $initializer);
    }

    /**
     * @param callable(T):void $initializer
     * @param string $field Can be a dot separated string to get a nested field
     */
    private function createLatestEloquentTaskFragmentInstance(
        string $field,
        ?callable $initializer = null,
    ): SchemaObject {
        $schemaVersion = $this->getFieldSchemaVersion((new EloquentCase())->getSchemaVersion(), 'tasks');

        return $this->createFragmentInstance($schemaVersion, $field, $initializer);
    }

    /**
     * @template T of FragmentModel&SchemaObject&SchemaProvider
     *
     * @param class-string<T> $fragment
     * @param callable(T):void $initializer
     * @param string $field Can be a dot separated string to get a nested field
     */
    private function createFragmentInstance(
        SchemaVersion $schemaVersion,
        string $field,
        ?callable $initializer = null,
    ): SchemaObject {
        $instance = $this->getFieldSchemaVersion($schemaVersion, $field)->newInstance();

        if ($initializer !== null) {
            call_user_func($initializer, $instance);
        }

        return $instance;
    }
}
