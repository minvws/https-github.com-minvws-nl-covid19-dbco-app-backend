<?php

declare(strict_types=1);

namespace App\Schema;

use App\Schema\Types\SchemaType;
use Closure;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use MinVWS\Codable\DecodingContext;
use MinVWS\Codable\EncodingContext;
use MinVWS\Codable\JSONDecoder;
use MinVWS\Codable\JSONEncoder;
use MinVWS\DBCO\Encryption\Security\Sealed;

use function array_key_exists;

/**
 * Adds encryption and casting support to entities.
 *
 * @implements SchemaProvider<static>
 */
abstract class Fragment extends Entity implements Castable, SchemaProvider
{
    use CachesSchema;

    /** @var array<class-string, CastsAttributes> */
    private static array $casters = [];

    private mixed $rawOriginal = null;

    /**
     * Load schema for this entity.
     */
    abstract protected static function loadSchema(): Schema;

    /**
     * Called after the fragment has been decrypted / decoded from the database.
     *
     * @param array $attributes
     */
    protected function postLoad(Model $model, array $attributes): void
    {
    }

    /**
     * Called before the fragment is encoded / encrypted and stored in the database.
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function preStore(Model $model, array $attributes): array
    {
        return [];
    }

    public function getRawOriginal(): mixed
    {
        return $this->rawOriginal;
    }

    public function setRawOriginal(mixed $rawOriginal): void
    {
        $this->rawOriginal = $rawOriginal;
    }

    /**
     * Responsible for loading the fragment from storage.
     *
     * @param Model $model Owner Eloquent model.
     * @param string $key Column / fragment name.
     * @param string|null $json Decrypted fragment JSON.
     * @param array $attributes All columns.
     *
     * @throws Exception
     */
    private static function castGet(Model $model, string $key, ?string $json, array $attributes): ?Fragment
    {
        if (!$model instanceof SchemaObject) {
            throw new Exception("Eloquent model \"" . $model::class . "\" is not a SchemaObject!");
        }

        if ($model->exists && !$model->wasRecentlyCreated && !array_key_exists($key, (array) $model->getRawOriginal())) {
            // Fragment column was never retrieved and this isn't a new row either.
            // Don't auto-create an empty fragment as we might have only retrieved
            // a limited set of columns for performance reasons.
            return null;
        }

        $fragmentName = Str::camel($key);

        $fragmentField = $model->getSchemaVersion()->getField($fragmentName);
        if ($fragmentField === null) {
            throw new Exception("Fragment \"{$fragmentName}\" not found in schema!");
        }

        /** @var SchemaType $fragmentType */
        $fragmentType = $fragmentField->getType();

        if ($json !== null) {
            $decoder = new JSONDecoder();
            $decoder->getContext()->setMode(DecodingContext::MODE_LOAD);
            $container = $decoder->decode($json);
            $fragment = $fragmentType->getSchemaVersion()->decode(
                $container,
                $fragmentType->getSchemaVersion()->newUninitializedInstance(),
            );
        } else {
            $fragment = $fragmentType->getSchemaVersion()->newInstance();
        }

        if (!$fragment instanceof Fragment) {
            throw new Exception("Fragment \"{$fragmentName}\"  could not be decoded / instantiated!");
        }

        $fragment->attachOwner($model);
        $fragment->postLoad($model, $attributes);
        $fragment->resetDirty();

        return $fragment;
    }

    /**
     * Responsible for preparing the fragment for storage.
     *
     * @param Model $model Owner Eloquent model.
     * @param string $key Column / fragment name.
     * @param Fragment $fragment Fragment
     * @param array $attributes All columns.
     *
     * @return array Prepared columns.
     *
     * @throws Exception
     */
    private static function castSet(Model $model, string $key, Fragment $fragment, array $attributes): array
    {
        if (!$model instanceof SchemaObject) {
            throw new Exception("Eloquent model \"" . $model::class . "\" is not a SchemaObject!");
        }

        $fragment->attachOwner($model);

        $result = $fragment->preStore($model, $attributes);

        $encoder = new JSONEncoder();
        $encoder->getContext()->setMode(EncodingContext::MODE_STORE);
        $result[$key] = $encoder->encode($fragment);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public static function castUsing(array $arguments)
    {
        $key = static::getSchema()->getClass();
        if (isset(self::$casters[$key])) {
            return self::$casters[$key];
        }

        $storageTerm = $arguments[0];
        $referenceDateTimeAttributeName = $arguments[1];

        $caster = new class ($storageTerm, $referenceDateTimeAttributeName, self::castGet(...), self::castSet(...)) extends Sealed {
            private Closure $castGet;
            private Closure $castSet;

            public function __construct(string $storageTerm, string $referenceDateTimeAttributeName, Closure $castGet, Closure $castSet)
            {
                parent::__construct($storageTerm, $referenceDateTimeAttributeName);

                $this->castGet = $castGet;
                $this->castSet = $castSet;
            }

            /**
             * @inheritdoc
             */
            public function get($model, string $key, $value, array $attributes): mixed
            {
                $json = parent::get($model, $key, $value, $attributes);

                $castGet = $this->castGet;
                $fragment = $castGet($model, $key, $json, $attributes);

                if ($fragment instanceof Fragment) {
                    $fragment->setRawOriginal($value);
                }

                return $fragment;
            }

            /**
             * @inheritdoc
             */
            public function set($model, string $key, $value, array $attributes): mixed
            {
                if (!$value instanceof Fragment) {
                    return [$key => null];
                }

                if (!$value->isDirty()) {
                    return [$key => $value->getRawOriginal()];
                }

                $castSet = $this->castSet;
                $result = $castSet($model, $key, $value, $attributes);

                $result[$key] = parent::set($model, $key, $result[$key], $attributes);
                $value->setRawOriginal($result[$key]);
                $value->resetDirty();

                return $result;
            }
        };

        self::$casters[$key] = $caster;
        return $caster;
    }
}
