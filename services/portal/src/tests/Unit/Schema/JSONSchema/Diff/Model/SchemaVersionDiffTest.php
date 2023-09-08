<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Schema\Descriptor;
use App\Schema\Generator\JSONSchema\Diff\Schema\EnumItem;
use App\Schema\Generator\JSONSchema\Diff\Schema\EnumVersion;
use App\Schema\Generator\JSONSchema\Diff\Schema\Property;
use App\Schema\Generator\JSONSchema\Diff\Schema\PropertyType;
use App\Schema\Generator\JSONSchema\Diff\Schema\SchemaVersion;
use MinVWS\Codable\Encoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

use function array_keys;
use function count;
use function property_exists;

#[Group('schema-jsonschema-diff')]
class SchemaVersionDiffTest extends UnitTestCase
{
    public static function unmodifiedProvider(): array
    {
        $schemaVersionDesc = Descriptor::forSchemaVersionId('/schemas/json/schemas/CovidCase/V1');
        $enumVersionDefDesc = Descriptor::forEnumVersionDefKey('Enum-YesNoUnknown-V1');
        $schemaVersionDefDesc = Descriptor::forSchemaVersionDefKey('CovidCase-General-V1');

        $property1 = new Property('prop1', null, PropertyType::type('string'), null);
        $property2 = new Property('prop2', null, PropertyType::type('string'), null);

        $enumVersionDef = new EnumVersion($enumVersionDefDesc, 'title', 'description', [new EnumItem('value', 'description')]);
        $schemaVersionDef = new SchemaVersion($schemaVersionDefDesc, 'title', 'description', [$property1], []);

        return [
            'equal' => [
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [], []),
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [], []),
            ],
            'equal-with-props' => [
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [$property1, $property2], []),
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [$property1, $property2], []),
            ],
            'equal-with-defs' => [
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [], [$enumVersionDef, $schemaVersionDef]),
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [], [$enumVersionDef, $schemaVersionDef]),
            ],
            'title-modified' => [
                new SchemaVersion($schemaVersionDesc, 'before', 'description', [$property1], [$enumVersionDef, $schemaVersionDef]),
                new SchemaVersion($schemaVersionDesc, 'after', 'description', [$property1], [$enumVersionDef, $schemaVersionDef]),
            ],
            'description-modified' => [
                new SchemaVersion($schemaVersionDesc, 'title', 'before', [$property1], [$enumVersionDef, $schemaVersionDef]),
                new SchemaVersion($schemaVersionDesc, 'title', 'after', [$property1], [$enumVersionDef, $schemaVersionDef]),
            ],
        ];
    }

    #[DataProvider('unmodifiedProvider')]
    public function testDiffShouldReturnNullIfUnmodified(SchemaVersion $original, SchemaVersion $new): void
    {
        $this->assertNull($new->diff($original));
        $this->assertNull($original->diff($new));
    }

    public static function modifiedProvider(): array
    {
        $schemaVersionDesc = Descriptor::forSchemaVersionId('/schemas/json/schemas/CovidCase/V1');
        $def1Desc = Descriptor::forEnumVersionId('/schemas/json/schemas/enums/YesNoUnknown/V1');
        $def2Desc = Descriptor::forEnumVersionId('/schemas/json/schemas/enums/BCOStatus/V1');

        $property1String = new Property('prop1', null, PropertyType::type('string'), null);
        $property1Int = new Property('prop1', null, PropertyType::type('int'), null);
        $property2 = new Property('prop2', null, PropertyType::type('string'), null);

        $def1 = new EnumVersion($def1Desc, 'title', 'description', [new EnumItem('value', 'description')]);
        $def1Modified = new EnumVersion(
            $def1Desc,
            'title',
            'description',
            [new EnumItem('value', 'description'), new EnumItem('another', 'description')],
        );
        $def2 = new EnumVersion($def2Desc, 'title', 'description', [new EnumItem('value', 'description')]);

        return [
            'property-added' => [
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [], []),
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [$property1String], []),
                [
                    $property1String->name => DiffType::Added,
                ],
                null,
            ],
            'property-removed' => [
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [$property1String], []),
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [], []),
                [
                    $property1String->name => DiffType::Removed,
                ],
                null,
            ],
            'property-modified' => [
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [$property1String], []),
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [$property1Int], []),
                [
                    $property1String->name => DiffType::Modified,
                ],
                null,
            ],
            'property-mix' => [
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [$property1String], []),
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [$property1Int, $property2], []),
                [
                    $property1String->name => DiffType::Modified,
                    $property2->name => DiffType::Added,
                ],
                null,
            ],
            'def-added' => [
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [], []),
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [], [$def1]),
                null,
                [
                    $def1->id => DiffType::Added,
                ],
            ],
            'def-removed' => [
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [], [$def1]),
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [], []),
                null,
                [
                    $def1->id => DiffType::Removed,
                ],
            ],
            'def-modified' => [
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [], [$def1]),
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [], [$def1Modified]),
                null,
                [
                    $def1->id => DiffType::Modified,
                ],
            ],
            'def-mix' => [
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [], [$def1]),
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [], [$def1Modified, $def2]),
                null,
                [
                    $def1->id => DiffType::Modified,
                    $def2->id => DiffType::Added,
                ],
            ],
            'mix' => [
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [$property1Int, $property2], [$def1]),
                new SchemaVersion($schemaVersionDesc, 'title', 'description', [$property1String], [$def1Modified, $def2]),
                [
                    $property1Int->name => DiffType::Modified,
                    $property2->name => DiffType::Removed,
                ],
                [
                    $def1->id => DiffType::Modified,
                    $def2->id => DiffType::Added,
                ],
            ],
        ];
    }

    #[DataProvider('modifiedProvider')]
    public function testDiffShouldReturnModified(SchemaVersion $original, SchemaVersion $new, ?array $expectedPropertyDiffs, ?array $expectedDefDiffs): void
    {
        $diff = $new->diff($original);

        $this->assertNotNull($diff);
        $this->assertEquals(DiffType::Modified, $diff->diffType);
        $this->assertSame($new, $diff->new);
        $this->assertSame($original, $diff->original);

        if ($expectedPropertyDiffs === null) {
            $this->assertNull($diff->propertyDiffs);
        } else {
            $this->assertCount(count($expectedPropertyDiffs), $diff->propertyDiffs);
            foreach ($expectedPropertyDiffs as $name => $diffType) {
                $this->assertArrayHasKey($name, $diff->propertyDiffs);
                $this->assertEquals($diffType, $diff->propertyDiffs[$name]->diffType);
            }
        }

        if ($expectedDefDiffs === null) {
            $this->assertNull($diff->defDiffs);
        } else {
            $this->assertCount(count($expectedDefDiffs), $diff->defDiffs);
            foreach ($expectedDefDiffs as $identifier => $diffType) {
                $this->assertArrayHasKey($identifier, $diff->defDiffs);
                $this->assertEquals($diffType, $diff->defDiffs[$identifier]->diffType);
            }
        }
    }

    #[DataProvider('modifiedProvider')]
    public function testEncode(SchemaVersion $original, SchemaVersion $new, ?array $expectedPropertyDiffs, ?array $expectedDefDiffs): void
    {
        $diff = $new->diff($original);
        $encoded = (new Encoder())->encode($diff);
        $this->assertEquals('modified', $encoded->diffType);
        $this->assertEquals($new->name ?? $original->name, $encoded->name);
        $this->assertEquals($new->version ?? $original->version, $encoded->version);
        $this->assertEquals($new->description ?? $original->description, $encoded->description);

        if ($expectedPropertyDiffs !== null) {
            $this->assertEquals(count($expectedPropertyDiffs), count($encoded->propertyDiffs));
            $this->assertEqualsCanonicalizing(array_keys($expectedPropertyDiffs), array_keys($encoded->propertyDiffs));
        } else {
            $this->assertFalse(property_exists($encoded, 'propertyDiffs'), 'Object property "propertyDiffs" should be missing.');
        }

        if ($expectedDefDiffs !== null) {
            $this->assertEquals(count($expectedDefDiffs), count($encoded->defDiffs));
            $this->assertEqualsCanonicalizing(array_keys($expectedDefDiffs), array_keys($encoded->defDiffs));
        } else {
            $this->assertFalse(property_exists($encoded, 'defDiffs'), 'Object property "defDiffs" should be missing.');
        }
    }

    public function testCompareNewDefToPreviousVersion(): void
    {
        $schemaVersionDesc = Descriptor::forSchemaVersionId('/schemas/json/schemas/CovidCase/V1');
        $enumDefV1Desc = Descriptor::forEnumVersionId('/schemas/json/schemas/enums/YesNoUnknown/V1');
        $enumDefV2Desc = Descriptor::forEnumVersionId('/schemas/json/schemas/enums/YesNoUnknown/V2');
        $schemaVersionDefV1Desc = Descriptor::forSchemaVersionId('/schemas/json/schemas/CovidCase/General/V1');
        $schemaVersionDefV2Desc = Descriptor::forSchemaVersionId('/schemas/json/schemas/CovidCase/General/V2');

        $property1 = new Property('prop1', null, PropertyType::type('string'), null);

        $enumDefV1 = new EnumVersion($enumDefV1Desc, 'title', 'description', [new EnumItem('a', 'description')]);
        $enumDefV2 = new EnumVersion($enumDefV2Desc, 'title', 'description', [new EnumItem('b', 'description')]);
        $schemaVersionDefV1 = new SchemaVersion($schemaVersionDefV1Desc, 'title', 'description', [], []);
        $schemaVersionDefV2 = new SchemaVersion($schemaVersionDefV2Desc, 'title', 'description', [$property1], []);

        $original = new SchemaVersion($schemaVersionDesc, 'title', 'description', [], [$enumDefV1, $schemaVersionDefV1]);
        $new = new SchemaVersion($schemaVersionDesc, 'title', 'description', [], [$enumDefV2, $schemaVersionDefV2]);

        $diff = $new->diff($original);

        // enum has been compared to previous version
        $this->assertArrayHasKey($enumDefV2Desc->id, $diff->defDiffs);
        $this->assertEquals(DiffType::Added, $diff->defDiffs[$enumDefV2Desc->id]->diffType);
        $this->assertEquals($enumDefV1, $diff->defDiffs[$enumDefV2Desc->id]->original);
        $this->assertNotNull($diff->defDiffs[$enumDefV2Desc->id]->itemDiffs);
        $this->assertNotEmpty($diff->defDiffs[$enumDefV2Desc->id]->itemDiffs);

        // schema version has been compared to previous version
        $this->assertArrayHasKey($schemaVersionDefV2Desc->id, $diff->defDiffs);
        $this->assertEquals(DiffType::Added, $diff->defDiffs[$schemaVersionDefV2Desc->id]->diffType);
        $this->assertEquals($schemaVersionDefV1, $diff->defDiffs[$schemaVersionDefV2Desc->id]->original);
        $this->assertNotNull($diff->defDiffs[$schemaVersionDefV2Desc->id]->propertyDiffs);
        $this->assertNotEmpty($diff->defDiffs[$schemaVersionDefV2Desc->id]->propertyDiffs);
    }
}
