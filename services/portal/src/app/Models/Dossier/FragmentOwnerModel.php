<?php

declare(strict_types=1);

namespace App\Models\Dossier;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Schema\SchemaObject;
use App\Schema\SchemaObjectFactory;
use App\Schema\SchemaVersion;
use App\Schema\Types\SchemaType;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

use function assert;
use function count;
use function in_array;

/**
 * @property-read DateTimeInterface $createdAt
 * @property-read DateTimeInterface $updatedAt
 */
abstract class FragmentOwnerModel extends Model implements SchemaObject, SchemaObjectFactory, Encodable, Decodable
{
    use CamelCaseAttributes {
        getAttribute as getAttributeCamelCase;
        setAttribute as setAttributeCamelCase;
    }

    private ?SchemaVersion $_schemaVersion = null;

    private array $fragmentsToCreate = [];
    private array $fragmentsToDelete = [];

    protected static function boot(): void
    {
        parent::boot();

        self::saved(static fn (self $model) => $model->saveFragments());
    }

    public static function newUninitializedInstanceWithSchemaVersion(SchemaVersion $schemaVersion): SchemaObject
    {
        $result = new static();
        $result->_schemaVersion = $schemaVersion;
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

    abstract protected function getTableColumns(): array;

    abstract protected function loadSchemaVersion(): SchemaVersion;

    abstract protected function associateFragmentWithOwner(FragmentModel $fragment): void;

    abstract public function fragments(): HasMany;

    public function getSchemaVersion(): SchemaVersion
    {
        return $this->_schemaVersion ?? $this->loadSchemaVersion();
    }

    public function getFragmentSchemaVersion(string $key): SchemaVersion
    {
        return $this->getSchemaVersion()
            ->getExpectedField($key)
            ->getExpectedType(SchemaType::class)
            ->getSchemaVersion();
    }

    private function isFragment(string $key): bool
    {
        $snakeKey = $this->getAttributeNameForKey($key);
        return !in_array($key, $this->getTableColumns(), true) &&
            !in_array($snakeKey, $this->getTableColumns(), true) &&
            !$this->isRelation($key) &&
            $this->getSchemaVersion()->getField($key) !== null &&
            $this->getSchemaVersion()->getField($key)->getType() instanceof SchemaType;
    }

    private function getFragment(string $key): FragmentModel
    {
        if (isset($this->fragmentsToCreate[$key])) {
            return $this->fragmentsToCreate[$key];
        }

        $fragment = $this->fragments->firstWhere('name', '=', $key);
        if ($fragment !== null) {
            assert($fragment instanceof FragmentModel);
            return $fragment;
        }

        $fragment = $this->fragments()->make(['name' => $key]);
        assert($fragment instanceof FragmentModel);
        $this->associateFragmentWithOwner($fragment);
        $this->fragmentsToCreate[$key] = $fragment;

        return $fragment;
    }

    private function setFragment(string $key, mixed $value): void
    {
        assert($value === null || $value instanceof FragmentModel);

        if (isset($this->fragmentsToCreate[$key])) {
            unset($this->fragmentsToCreate[$key]);
        }

        $oldFragment = $this->fragments->firstWhere('name', '=', $key);
        if ($oldFragment !== null) {
            $this->fragmentsToDelete[] = $oldFragment;
        }

        if ($value === null) {
            return;
        }

        $this->associateFragmentWithOwner($value);
        $this->fragmentsToCreate[$key] = $value;
    }

    private function saveFragments(): void
    {
        foreach ($this->fragmentsToDelete as $fragment) {
            $fragment->delete();
        }

        if (count($this->fragmentsToCreate) === 0) {
            return;
        }

        $this->fragments()->saveMany($this->fragmentsToCreate);
        $this->fragmentsToCreate = [];
        $this->unsetRelation('fragments');
    }

    public function getAttribute(mixed $key): mixed
    {
        if ($this->isFragment($key)) {
            return $this->getFragment($key);
        }

        return $this->getAttributeCamelCase($key);
    }

    public function setAttribute(mixed $key, mixed $value): self
    {
        if ($this->isFragment($key)) {
            $this->setFragment($key, $value);
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
