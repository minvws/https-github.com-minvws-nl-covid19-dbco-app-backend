<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Schema;

use ValueError;

use function preg_match;
use function str_replace;

/**
 * Used for parsing references and definition identifiers.
 *
 * Depending on the UseCompoundSchemas setting these can either be absolute URL paths or
 * internal JSON pointers.
 */
class Descriptor
{
    public readonly string $id;

    public function __construct(public readonly string $name, public readonly int $version)
    {
        $this->id = $name . '-V' . $version;
    }

    public static function forRef(string $ref): Descriptor
    {
        if (preg_match('|^.*/schemas/enums/(.+?)/V(\d)$|', $ref, $matches) === 1) {
            return self::forEnumVersionId($ref);
        }

        if (preg_match('|^.*/schemas/(.+?)/V(\d)$|', $ref, $matches) === 1) {
            return self::forSchemaVersionId($ref);
        }

        if (preg_match('|^#/\$defs/(.+)?-V(\d+)$|', $ref, $matches) === 1) {
            return new self($matches[1], (int) $matches[2]);
        }

        throw new ValueError('Invalid reference value');
    }

    public static function forSchemaVersionId(string $id): Descriptor
    {
        if (preg_match('|^.*/schemas/(.+?)/V(\d)$|', $id, $matches) !== 1) {
            throw new ValueError('Invalid schema version identifier');
        }

        return new self(str_replace('/', '-', $matches[1]), (int) $matches[2]);
    }

    public static function forSchemaVersionDefKey(string $key): Descriptor
    {
        if (preg_match('/^(.+)?-V(\d+)$/', $key, $matches) !== 1) {
            throw new ValueError('Invalid schema version definition key');
        }

        return new self($matches[1], (int) $matches[2]);
    }

    public static function forEnumVersionId(string $id): Descriptor
    {
        if (preg_match('|^.*/schemas/enums/(.+?)/V(\d)$|', $id, $matches) !== 1) {
            throw new ValueError('Invalid enum version identifier');
        }

        return new self('Enum-' . $matches[1], (int) $matches[2]);
    }

    public static function forEnumVersionDefKey(string $key): Descriptor
    {
        if (preg_match('/^(.+)?-V(\d+)$/', $key, $matches) !== 1) {
            throw new ValueError('Invalid enum version definition key');
        }

        return new self($matches[1], (int) $matches[2]);
    }
}
