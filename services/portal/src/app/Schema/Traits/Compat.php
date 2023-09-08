<?php

declare(strict_types=1);

namespace App\Schema\Traits;

use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\SchemaVersion;
use App\Schema\Validation\ValidationContext;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\DecodingContext;

use function assert;
use function is_null;

/**
 * Compatibility for validation and decoding in fragment service.
 *
 * @implements SchemaProvider<static>
 */
trait Compat
{
    /**
     * Try to extract schema version from decoding container.
     */
    protected static function getSchemaVersionForDecodingContainer(DecodingContainer $container): ?SchemaVersion
    {
        $schemaVersionField = static::getSchema()->getSchemaVersionField();
        if ($schemaVersionField === null) {
            return null;
        }

        $schemaVersionFieldName = $schemaVersionField->getName();

        if (!$container->contains($schemaVersionFieldName)) {
            return null;
        }

        try {
            $version = $container->$schemaVersionFieldName->decodeInt();
            return static::getSchema()->getVersion($version);
        } catch (CodableException $e) {
            return null;
        }
    }

    /**
     * Try to extract schema version from data.
     *
     * @param array $data
     */
    protected static function getSchemaVersionForData(array $data): ?SchemaVersion
    {
        try {
            $decoder = new Decoder();
            $decoder->getContext()->setMode(DecodingContext::MODE_INPUT);
            $container = $decoder->decode($data);
            return static::getSchemaVersionForDecodingContainer($container);
        } catch (CodableException $e) {
            return null;
        }
    }

    /**
     * @inheritDoc
     *
     * @deprecated Placeholder: No description was set at the time.
     */
    public static function validationRules(array $data): array
    {
        $schemaVersion = static::getSchemaVersionForData($data) ?? static::getSchema()->getCurrentVersion();
        $validationRules = $schemaVersion->getValidationRules();

        $context = new ValidationContext();
        $context->setData($data);

        $levels = [
            ValidationContext::FATAL,
            ValidationContext::WARNING,
            ValidationContext::NOTICE,
            ValidationContext::OSIRIS_APPROVED,
            ValidationContext::OSIRIS_FINISHED,
        ];
        $result = [];
        foreach ($levels as $level) {
            $context->setLevel($level);
            $result[$level] = $validationRules->make($context);
        }

        return $result;
    }

    /**
     * @inheritDoc
     *
     * @codeCoverageIgnore
     *
     * @deprecated Placeholder: No description was set at the time.
     */
    public static function decode(DecodingContainer $container, ?object $object = null): ?self
    {
        if (!$container->isPresent()) {
            return null;
        }

        $schemaVersion = static::getSchemaVersionForDecodingContainer($container);
        if ($schemaVersion === null && $object instanceof static) {
            $schemaVersion = $object->getSchemaVersion();
        } elseif ($schemaVersion === null) {
            $schemaVersion = static::getSchema()->getCurrentVersion();
        } elseif ($object instanceof static && (string) $object->getSchemaVersion() !== (string) $schemaVersion) {
            throw new CodableException('Decoded schema version does not match target schema version!');
        }


        assert(is_null($object) || $object instanceof SchemaObject);
        /** @var static $fragment */
        $fragment = $schemaVersion->decode($container, $object);
        return $fragment;
    }
}
