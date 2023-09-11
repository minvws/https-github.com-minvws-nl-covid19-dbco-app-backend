<?php

declare(strict_types=1);

namespace App\Schema;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Schema\Fields\Field;
use App\Schema\Traits\NewInstanceWithVersion;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use InvalidArgumentException;
use MinVWS\Codable\DecodingContext;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\EncodingContext;
use MinVWS\Codable\JSONDecoder;
use MinVWS\Codable\JSONEncoder;
use MinVWS\DBCO\Encryption\Security\CacheEntryNotFoundException;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use RuntimeException;
use stdClass;
use Throwable;
use Webmozart\Assert\Assert;

use function app;
use function array_reduce;
use function assert;
use function explode;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;
use function str_replace;

/**
 * @template T of SchemaObject
 *
 * @implements SchemaProvider<static>
 */
abstract class FragmentModel extends Model implements SchemaObject, SchemaObjectFactory, SchemaProvider, Encodable
{
    use CachesSchema;
    use CamelCaseAttributes;
    use NewInstanceWithVersion;

    private stdClass $fragmentData;

    protected static ?string $nameAttribute = 'fragment_name';
    protected static string $dataAttribute = 'data';
    protected static string $schemaVersionAttribute = 'schema_version';
    protected static string $encryptionReferenceDateAttribute = 'encryption_reference_date';
    protected static ?string $encryptionExpiresAtAttribute = 'expires_at';
    protected static string $storageTerm = StorageTerm::SHORT;

    /**
     * @return Schema|Schema<static>
     */
    abstract protected static function loadSchema(): Schema;

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(static function (Builder $builder): void {
            $model = $builder->getModel();
            if ($model instanceof self) {
                $model->applyBaseFilter($builder);
            }
        });
        static::saving(static fn (self $model) => $model->onSaving());
    }

    final protected function __construct(array $attributes = [])
    {
        $this->fragmentData = new stdClass();

        $version = $attributes[static::$schemaVersionAttribute] ?? static::getSchema()->getCurrentVersion()->getVersion();
        unset($attributes[static::$schemaVersionAttribute]);

        parent::__construct($attributes);

        $this->{static::$schemaVersionAttribute} = $version;

        if (isset(static::$nameAttribute)) {
            $this->{static::$nameAttribute} = static::getFragmentName();
        }
    }

    /**
     * @inheritdoc
     */
    final public function newInstance($attributes = [], $exists = false)
    {
        $version = $attributes[static::$schemaVersionAttribute] ?? static::getSchema()->getCurrentVersion()->getVersion();
        unset($attributes[static::$schemaVersionAttribute]);
        $schemaVersion = static::getSchema()->getVersion($version);

        /** @var static $model */
        $model = $exists ? $schemaVersion->newUninitializedInstance() : $schemaVersion->newInstance();
        $model->fill($attributes);
        $model->exists = $exists;
        $model->setConnection($this->getConnectionName());
        $model->setTable($this->getTable());
        $model->mergeCasts($this->casts);
        return $model;
    }

    /**
     * Create a new model instance that is existing.
     *
     * @inheritdoc
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $newInstanceAttrs = [];
        if (is_object($attributes) && isset($attributes->{static::$schemaVersionAttribute})) {
            $newInstanceAttrs[static::$schemaVersionAttribute] = $attributes->{static::$schemaVersionAttribute};
        } elseif (is_array($attributes) && isset($attributes[static::$schemaVersionAttribute])) {
            $newInstanceAttrs[static::$schemaVersionAttribute] = $attributes[static::$schemaVersionAttribute];
        }

        $model = $this->newInstance($newInstanceAttrs, true);
        $model->setRawAttributes((array) $attributes, true);
        $model->setConnection($connection ?: $this->getConnectionName());
        $model->fireModelEvent('retrieved', false);

        return $model;
    }

    protected static function getFragmentName(): string
    {
        return static::getSchema()->getName();
    }

    protected function applyBaseFilter(Builder $builder): void
    {
        if (isset(static::$nameAttribute)) {
            $builder->where(static::$nameAttribute, '=', static::getFragmentName());
        }
    }

    protected function onSaving(): void
    {
        $this->updateFragmentData();
    }

    protected function getReferenceDate(): DateTimeInterface
    {
        $referenceDate = array_reduce(
            explode('.', static::$encryptionReferenceDateAttribute),
            static function ($object, $property) {
                if (!$object instanceof Model) {
                    return null;
                }

                $result = $object->$property ?? null;
                if ($result !== null) {
                    return $result;
                }

                $relation = $object->$property();
                if (!$relation instanceof BelongsTo) {
                    return null;
                }

                // make sure the belongs to relation is loaded without any scopes
                return $relation->withoutGlobalScopes()->first();
            },
            $this,
        );

        if (is_string($referenceDate)) {
            try {
                $referenceDate = new DateTimeImmutable($referenceDate);
            } catch (Throwable $e) {
                throw new RuntimeException($e->getMessage(), 0, $e);
            }
        }

        if (!$referenceDate instanceof DateTimeInterface) {
            throw new RuntimeException(
                sprintf(
                    'No value for encryption reference date attribute %s->%s',
                    static::class,
                    str_replace('.', '->', static::$encryptionReferenceDateAttribute),
                ),
            );
        }

        return $referenceDate;
    }

    public function getFragmentData(): object
    {
        return $this->fragmentData;
    }

    private function loadFragmentData(): void
    {
        $this->fragmentData = new stdClass();
        $data = $this->getAttributeValue(static::$dataAttribute);

        if ($data === null) {
            return;
        }
        assert(is_string($data));

        $this->decodeFragmentData($data, $this);
    }

    private function updateFragmentData(): void
    {
        $storageTerm = StorageTerm::forValue(static::$storageTerm);
        $referenceDate = $this->getReferenceDate();

        $encoder = new JSONEncoder();
        $encoder->getContext()->setMode(EncodingContext::MODE_STORE);
        $json = $encoder->encode($this);

        $encryptionHelper = app()[EncryptionHelper::class];
        $data = $encryptionHelper->sealOptionalStoreValue($json, $storageTerm, $referenceDate);
        $this->{static::$dataAttribute} = $data;

        if (!isset(static::$encryptionExpiresAtAttribute)) {
            return;
        }

        $expirationDate = $storageTerm->expirationDateForReferenceDate($referenceDate);
        $this->{static::$encryptionExpiresAtAttribute} = $expirationDate;
    }

    public function getSchemaVersion(): SchemaVersion
    {
        return static::getSchema()->getVersion($this->{self::$schemaVersionAttribute} ?? 1);
    }

    public static function newUninitializedInstanceWithSchemaVersion(SchemaVersion $schemaVersion): SchemaObject
    {
        return new static([static::$schemaVersionAttribute => $schemaVersion->getVersion()]);
    }

    public function encode(EncodingContainer $container): void
    {
        $this->getSchemaVersion()->encode($container, $this);
    }

    /**
     * @inheritDoc
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        parent::setRawAttributes($attributes, $sync);

        $this->loadFragmentData();
        return $this;
    }

    private function getFragmentFieldForKey(string $key): ?Field
    {
        // returns a field if it exists for this schema version and isn't marked as external
        // (e.g. part of the table itself) or is marked as external, but is a proxy field
        $field = $this->getSchemaVersion()->getField($key);
        return $field !== null && (!$field->isExternal() || $field->isProxyForOwnerField()) ? $field : null;
    }

    protected function getAttributeNameForKey(string $key): string
    {
        // shouldn't snake-case fields that are part of the schema
        return $this->getFragmentFieldForKey($key) !== null ? $key : Str::snake($key);
    }

    /**
     * @inheritDoc
     */
    public function hasGetMutator($key): bool
    {
        return $this->getFragmentFieldForKey($key) !== null || parent::hasGetMutator($key);
    }

    private function getOwnerForProxyField(Field $field): Model
    {
        $ownerRelation = $field->getSchema()->getOwnerFieldName();
        $ownerFieldName = $field->getProxyForOwnerField();

        $owner = $field->$ownerRelation;
        if (!$owner instanceof Model) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s->%s->%s cannot be proxied (%s null or not a model)',
                    static::class,
                    $ownerRelation,
                    $ownerFieldName,
                    $ownerRelation,
                ),
            );
        }

        return $owner;
    }

    private function getOwnerFieldValue(Field $field): mixed
    {
        $owner = $this->getOwnerForProxyField($field);
        $ownerFieldName = $field->getProxyForOwnerField();
        return $owner->$ownerFieldName ?? null;
    }

    private function setOwnerFieldValue(Field $field, mixed $value): void
    {
        $owner = $this->getOwnerForProxyField($field);
        $ownerFieldName = $field->getProxyForOwnerField();
        $owner->$ownerFieldName = $value;
    }

    /**
     * @inheritDoc
     */
    public function mutateAttribute($key, $value)
    {
        if (parent::hasGetMutator($key)) {
            return parent::mutateAttribute($key, $value);
        }

        $field = $this->getFragmentFieldForKey($key);
        if ($field === null) {
            return parent::mutateAttribute($key, $value);
        }

        if ($field->isProxyForOwnerField()) {
            return $this->getOwnerFieldValue($field);
        }

        return $field->assignedValue($this->getFragmentData());
    }

    /**
     * @inheritDoc
     */
    public function hasSetMutator($key): bool
    {
        return $this->getFragmentFieldForKey($key) !== null || parent::hasSetMutator($key);
    }

    /**
     * @inheritDoc
     */
    protected function setMutatedAttributeValue($key, $value)
    {
        if (parent::hasSetMutator($key)) {
            return parent::setMutatedAttributeValue($key, $value);
        }

        $field = $this->getFragmentFieldForKey($key);

        if ($field === null) {
            return parent::setMutatedAttributeValue($key, $value);
        }

        if ($field->isProxyForOwnerField()) {
            $this->setOwnerFieldValue($field, $value);
        } else {
            $field->assign($this->getFragmentData(), $value);
        }

        return $this;
    }

    public function getOriginalFragmentData(): ?SchemaObject
    {
        $originalData = $this->getOriginal(static::$dataAttribute);

        if ($originalData === null) {
            return null;
        }
        assert(is_string($originalData));

        return $this->decodeFragmentData($originalData);
    }

    /**
     * @param static|null $target
     */
    private function decodeFragmentData(string $data, ?SchemaObject $target = null): ?SchemaObject
    {
        $encryptionHelper = Container::getInstance()[EncryptionHelper::class];
        Assert::isInstanceOf($encryptionHelper, EncryptionHelper::class);

        try {
            $json = $encryptionHelper->unsealOptionalStoreValue($data);
        } catch (CacheEntryNotFoundException) {
            // fragment expired, don't load data
            return null;
        }

        if ($json === null) {
            return null;
        }

        $decoder = new JSONDecoder();
        $decoder->getContext()->setMode(DecodingContext::MODE_LOAD);
        $container = $decoder->decode($json);

        return $this->getSchemaVersion()->decode($container, $target);
    }

    protected function assignFieldValue(string $fieldName, mixed $value): void
    {
        $field = $this->getSchemaVersion()->getExpectedField($fieldName);
        $field->assign($this->getFragmentData(), $value);
    }

    protected function assignedFieldValue(string $fieldName): mixed
    {
        return $this->getSchemaVersion()->getExpectedField($fieldName)->assignedValue($this->getFragmentData());
    }
}
