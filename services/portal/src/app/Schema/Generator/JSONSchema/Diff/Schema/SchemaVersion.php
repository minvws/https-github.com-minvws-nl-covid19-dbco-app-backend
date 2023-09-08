<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffList;
use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Model\EnumVersionDiff;
use App\Schema\Generator\JSONSchema\Diff\Model\PropertyDiff;
use App\Schema\Generator\JSONSchema\Diff\Model\SchemaVersionDiff;
use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;

use function array_combine;
use function array_keys;
use function array_map;
use function array_merge;
use function array_unique;
use function ksort;

use const SORT_STRING;

class SchemaVersion implements Decodable
{
    public readonly string $id;
    public readonly string $name;
    public readonly int $version;

    /** @var array<string, Property> */
    public readonly array $properties;

    /** @var array<string, SchemaVersion|EnumVersion> */
    public readonly array $defs;

    /**
     * @param array<Property> $properties
     * @param array<SchemaVersion|EnumVersion> $defs
     */
    public function __construct(public readonly Descriptor $descriptor, public readonly ?string $title, public readonly ?string $description, array $properties, array $defs)
    {
        $this->id = $descriptor->id;
        $this->name = $descriptor->name;
        $this->version = $descriptor->version;

        $indexedProperties = array_combine(array_map(static fn ($p) => $p->name, $properties), $properties);
        ksort($indexedProperties, SORT_STRING);
        $this->properties = $indexedProperties;

        $indexedDefs = array_combine(array_map(static fn ($d) => $d->id, $defs), $defs);
        ksort($indexedDefs, SORT_STRING);
        $this->defs = $indexedDefs;
    }

    public static function decode(DecodingContainer $container, ?object $object = null): self
    {
        $descriptor = !$container->contains('$id') && $container->getKey() !== null
            ? Descriptor::forSchemaVersionDefKey((string) $container->getKey())
            : Descriptor::forSchemaVersionId(
                $container->{'$id'}->decodeString(),
            );

        $title = $container->{'title'}->decodeStringIfPresent();
        $description = $container->{'description'}->decodeStringIfPresent();
        $properties = $container->{'properties'}->decodeArray(Property::class);
        $defs = $container->{'$defs'}->decodeArrayIfPresent(static function (DecodingContainer $defContainer) {
            if ($defContainer->contains('oneOf')) {
                return $defContainer->decodeObject(EnumVersion::class);
            }

            return $defContainer->decodeObject(SchemaVersion::class);
        });

        return new self($descriptor, $title, $description, $properties, $defs ?? []);
    }

    public function getDefForRef(string $ref): SchemaVersion|EnumVersion|null
    {
        $descriptor = Descriptor::forRef($ref);
        return $this->defs[$descriptor->id] ?? null;
    }

    private function getPropertyNames(): array
    {
        return array_keys($this->properties);
    }

    private function getProperty(string $name): ?Property
    {
        return $this->properties[$name] ?? null;
    }

    private function diffProperties(SchemaVersion $original): ?DiffList
    {
        $propertyNames = array_unique(array_merge($this->getPropertyNames(), $original->getPropertyNames()));

        $propertyDiffs = new DiffList();
        foreach ($propertyNames as $propertyName) {
            $newProperty = $this->getProperty($propertyName);
            $originalProperty = $original->getProperty($propertyName);

            if (isset($newProperty) && isset($originalProperty)) {
                $diff = $newProperty->diff($originalProperty);
                if ($diff !== null) {
                    $propertyDiffs[$propertyName] = $diff;
                }
            } elseif (isset($newProperty)) {
                $propertyDiffs[$propertyName] = new PropertyDiff(DiffType::Added, $newProperty, null, null, null);
            } elseif (isset($originalProperty)) {
                $propertyDiffs[$propertyName] = new PropertyDiff(DiffType::Removed, null, $originalProperty, null, null);
            }
        }

        return $propertyDiffs->isEmpty() ? null : $propertyDiffs;
    }

    private function getDefIds(): array
    {
        return array_keys($this->defs);
    }

    private function getDef(string $id): SchemaVersion|EnumVersion|null
    {
        return $this->defs[$id] ?? null;
    }

    private function getDefByNameAndVersion(string $name, int $version): SchemaVersion|EnumVersion|null
    {
        foreach ($this->defs as $def) {
            if ($def->name === $name && $def->version === $version) {
                return $def;
            }
        }

        return null;
    }

    private function diffSchemaVersionDefs(SchemaVersion $original, ?SchemaVersion $newDef, ?SchemaVersion $originalDef): ?SchemaVersionDiff
    {
        if (isset($newDef) && isset($originalDef)) {
            return $newDef->diff($originalDef);
        }

        if (isset($newDef)) {
            // try to compare to previous version
            $originalDef = $original->getDefByNameAndVersion($newDef->name, $newDef->version - 1);
            $diff = $originalDef instanceof SchemaVersion ? $newDef->diff($originalDef) : null;
            return new SchemaVersionDiff(DiffType::Added, $newDef, $diff?->original, $diff?->propertyDiffs, $diff?->defDiffs);
        }

        return new SchemaVersionDiff(DiffType::Removed, null, $originalDef, null, null);
    }

    private function diffEnumVersionDefs(SchemaVersion $original, ?EnumVersion $newDef, ?EnumVersion $originalDef): ?EnumVersionDiff
    {
        if (isset($newDef) && isset($originalDef)) {
            return $newDef->diff($originalDef);
        }

        if (isset($newDef)) {
            // try to compare to previous version
            $originalDef = $original->getDefByNameAndVersion($newDef->name, $newDef->version - 1);
            $diff = $originalDef instanceof EnumVersion ? $newDef->diff($originalDef) : null;
            return new EnumVersionDiff(DiffType::Added, $newDef, $diff?->original, $diff?->itemDiffs);
        }

        return new EnumVersionDiff(DiffType::Removed, null, $originalDef, null);
    }

    private function diffDefs(SchemaVersion $original): ?DiffList
    {
        $defIds = array_unique(array_merge($this->getDefIds(), $original->getDefIds()));

        $defDiffs = new DiffList();
        foreach ($defIds as $defId) {
            $newDef = $this->getDef($defId);
            $originalDef = $original->getDef($defId);

            $defDiff = null;
            if (
                (
                    $newDef === null
                    || $newDef instanceof SchemaVersion
                )
                &&
                (
                    $originalDef === null
                    || $originalDef instanceof SchemaVersion
                )
            ) {
                $defDiff = $this->diffSchemaVersionDefs($original, $newDef, $originalDef);
            } elseif (
                (
                    $newDef === null
                    || $newDef instanceof EnumVersion
                )
                &&
                (
                    $originalDef === null
                    || $originalDef instanceof EnumVersion
                )
            ) {
                $defDiff = $this->diffEnumVersionDefs($original, $newDef, $originalDef);
            }

            if ($defDiff !== null) {
                $defDiffs[$defId] = $defDiff;
            }
        }

        return $defDiffs->isEmpty() ? null : $defDiffs;
    }

    public function diff(SchemaVersion $original): ?SchemaVersionDiff
    {
        $propertyDiffs = $this->diffProperties($original);
        $defDiffs = $this->diffDefs($original);

        if ($propertyDiffs === null && $defDiffs === null) {
            return null;
        }

        return new SchemaVersionDiff(DiffType::Modified, $this, $original, $propertyDiffs, $defDiffs);
    }
}
