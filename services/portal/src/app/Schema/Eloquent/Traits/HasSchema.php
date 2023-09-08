<?php

declare(strict_types=1);

namespace App\Schema\Eloquent\Traits;

use App\Schema\CachesSchema;
use App\Schema\Fragment;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaVersion;
use App\Schema\Traits\NewInstanceWithVersion;
use Illuminate\Database\Eloquent\Model;
use MinVWS\Codable\EncodingContainer;

use function is_int;

trait HasSchema
{
    use CachesSchema;
    use NewInstanceWithVersion;

    /** @var array<int, Model> */
    private static $instanceFactories = [];

    final public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Attribute name to store/retrieve the schema version for this model in.
     */
    protected static function getSchemaVersionAttributeName(): string
    {
        return 'schema_version';
    }

    public static function newUninitializedInstanceWithSchemaVersion(SchemaVersion $schemaVersion): SchemaObject
    {
        $attrName = static::getSchemaVersionAttributeName();
        /** @var class-string<self> $class */
        $class = $schemaVersion->getClass();
        $model = new $class();
        $model->$attrName = $schemaVersion->getVersion();
        return $model;
    }

    protected static function postLoadSchema(Schema $schema): void
    {
        $schema->setObjectFactory(
            static fn (SchemaVersion $schemaVersion) =>
                static::newUninitializedInstanceWithSchemaVersion($schemaVersion)
        );
    }

    public function getSchemaVersion(): SchemaVersion
    {
        $attrName = static::getSchemaVersionAttributeName();
        if (!empty($this->$attrName) && is_int($this->$attrName)) {
            return static::getSchema()->getVersion($this->$attrName);
        }

        return static::getSchema()->getCurrentVersion();
    }

    private function getNewInstanceFactory(array $attributes): self
    {
        // There is a fairly big chance that Eloquent calls the newInstance() method on the base class and not on
        // the (correct) versioned subclass. So we first retrieve an instance of the correct versioned subclass and
        // use that as factory to call the newInstance() method on. We cache these factory instances so we don't create
        // a new instance of every object retrieved from the database.
        $version = (int) ($attributes[static::getSchemaVersionAttributeName()] ?? static::getSchema()->getCurrentVersion()->getVersion());
        if (!isset(self::$instanceFactories[$version])) {
            self::$instanceFactories[$version] = static::newInstanceWithVersion($version);
        }

        /** @var self $factory */
        $factory = self::$instanceFactories[$version];
        return $factory;
    }

    private bool $forwardToParent = false;

    /**
     * @template T
     *
     * @param callable(): T $closure
     *
     * @return T
     */
    private function forwardToParent(callable $closure): mixed
    {
        if ($this->forwardToParent) {
            return $closure();
        }

        try {
            $this->forwardToParent = true;
            $result = $closure();
        } finally {
            $this->forwardToParent = false;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    final public function newInstance($attributes = [], $exists = false): self
    {
        if ($this->forwardToParent) {
            return parent::newInstance($attributes, $exists);
        }

        $factory = $this->getNewInstanceFactory($attributes);
        return $factory->forwardToParent(static fn () => $factory->newInstance($attributes, $exists));
    }

    /**
     * @inheritdoc
     */
    final public function newFromBuilder($attributes = [], $connection = null): self
    {
        if ($this->forwardToParent) {
            return parent::newFromBuilder($attributes, $connection);
        }

        $factory = $this->getNewInstanceFactory((array) $attributes);
        return $factory->forwardToParent(static fn () => $factory->newFromBuilder($attributes, $connection));
    }

    /**
     * @inheritdoc
     */
    protected function setClassCastableAttribute($key, $value): void
    {
        // fragments need to attach their owner as early as possible, so they can apply
        // any pending attribute updates on the owner
        if ($value instanceof Fragment) {
            $value->attachOwner($this);
        }

        parent::setClassCastableAttribute($key, $value);
    }

    public function getForeignKey(): string
    {
        // don't use class name as it may differ per version
        return $this->getTable() . '_' . $this->getKeyName();
    }

    public function encode(EncodingContainer $container): void
    {
        $this->getSchemaVersion()->encode($container, $this);
    }
}
