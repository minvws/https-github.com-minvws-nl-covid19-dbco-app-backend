<?php

declare(strict_types=1);

namespace App\Models\Dossier;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Schema\SchemaObject;
use App\Schema\SchemaObjectFactory;
use App\Schema\SchemaVersion;
use DateTimeInterface;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\DecodingContext;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\EncodingContext;
use MinVWS\Codable\JSONDecoder;
use MinVWS\Codable\JSONEncoder;
use MinVWS\DBCO\Encryption\Security\CacheEntryNotFoundException;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use stdClass;

use function array_merge;
use function in_array;

/**
 * @property string $name
 * @property string $data
 * @property DateTimeInterface $createdAt
 * @property DateTimeInterface $updatedAt
 * @property DateTimeInterface $expiresAt
 *
 * @property-read int $id
 */
abstract class FragmentModel extends Model implements SchemaObject, SchemaObjectFactory, Encodable, Decodable
{
    use CamelCaseAttributes {
        getAttribute as getAttributeCamelCase;
        setAttribute as setAttributeCamelCase;
    }

    private const TABLE_COLUMNS = ['id', 'name', 'data', 'created_at', 'updated_at', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $fillable = [
        'name',
    ];

    private ?SchemaVersion $schemaVersion = null;
    private ?stdClass $fragmentData = null;

    protected static function boot(): void
    {
        parent::boot();

        static::saving(static fn (self $model) => $model->updateFragmentData());
    }

    public static function newUninitializedInstanceWithSchemaVersion(SchemaVersion $schemaVersion): SchemaObject
    {
        $result = new static();
        $result->schemaVersion = $schemaVersion;
        return $result;
    }

    final public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->init();
    }

    protected function init(): void
    {
    }

    abstract protected static function getStorageTerm(): StorageTerm;

    abstract protected static function getOwnerTableColumn(): string;

    abstract protected function loadSchemaVersion(): SchemaVersion;

    abstract protected function getEncryptionReferenceDate(): DateTimeInterface;

    protected static function getTableColumns(): array
    {
        return array_merge(self::TABLE_COLUMNS, [static::getOwnerTableColumn()]);
    }

    public function getSchemaVersion(): SchemaVersion
    {
        return $this->schemaVersion ?? $this->loadSchemaVersion();
    }

    private function getFragmentData(): object
    {
        if ($this->fragmentData !== null) {
            return $this->fragmentData;
        }

        return $this->loadFragmentData();
    }

    private function loadFragmentData(): stdClass
    {
        $fragmentData = new stdClass();
        $this->fragmentData = $fragmentData;

        if ($this->data === null) {
            return $fragmentData;
        }

        /** @var EncryptionHelper $encryptionHelper */
        $encryptionHelper = Container::getInstance()[EncryptionHelper::class];

        try {
            $json = $encryptionHelper->unsealOptionalStoreValue($this->data);
        } catch (CacheEntryNotFoundException) {
            // fragment expired, don't load data
            return $fragmentData;
        }

        if ($json === null) {
            // null fragment
            return $fragmentData;
        }

        $decoder = new JSONDecoder();
        $decoder->getContext()->setMode(DecodingContext::MODE_LOAD);
        $container = $decoder->decode($json);
        $this->getSchemaVersion()->decode($container, $this);

        return $fragmentData;
    }

    private function updateFragmentData(): void
    {
        $storageTerm = static::getStorageTerm();
        $referenceDate = $this->getEncryptionReferenceDate();

        $encoder = new JSONEncoder();
        $encoder->getContext()->setMode(EncodingContext::MODE_STORE);
        $json = $encoder->encode($this);

        $encryptionHelper = Container::getInstance()[EncryptionHelper::class];

        $data = $encryptionHelper->sealOptionalStoreValue($json, $storageTerm, $referenceDate);
        $this->data = $data;

        $this->expiresAt = $storageTerm->expirationDateForReferenceDate($referenceDate);
    }

    private function isField(string $key): bool
    {
        $snakeKey = $this->getAttributeNameForKey($key);
        return !in_array($key, static::getTableColumns(), true) &&
            !in_array($snakeKey, static::getTableColumns(), true) &&
            !$this->isRelation($key) &&
            $this->getSchemaVersion()->getField($key) !== null;
    }

    private function getFieldValue(string $key): mixed
    {
        return $this->getSchemaVersion()->getExpectedField($key)->assignedValue($this->getFragmentData());
    }

    private function setFieldValue(string $key, mixed $value): void
    {
        $this->getSchemaVersion()->getExpectedField($key)->assign($this->getFragmentData(), $value);
    }

    public function getAttribute(mixed $key): mixed
    {
        if ($this->isField($key)) {
            return $this->getFieldValue($key);
        }

        return $this->getAttributeCamelCase($key);
    }

    public function setAttribute(mixed $key, mixed $value): self
    {
        if ($this->isField($key)) {
            $this->setFieldValue($key, $value);
        } else {
            $this->setAttributeCamelCase($key, $value);
        }

        return $this;
    }

    public function encode(EncodingContainer $container): void
    {
        $this->getSchemaVersion()->encode($container, $this);
    }

    /**
     * @return static
     */
    public static function decode(DecodingContainer $container, ?object $object = null): static
    {
        if ($object instanceof static) {
            $object->getSchemaVersion()->decode($container, $object);
            return $object;
        }

        throw new CodableException('Decoding is only possible for this class when an object is supplied!');
    }
}
