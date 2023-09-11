<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Schema\Descriptor;
use App\Schema\Generator\JSONSchema\Diff\Schema\EnumItem;
use App\Schema\Generator\JSONSchema\Diff\Schema\EnumVersion;
use App\Schema\Generator\JSONSchema\Diff\Schema\Property;
use App\Schema\Generator\JSONSchema\Diff\Schema\PropertyType;
use App\Schema\Generator\JSONSchema\Diff\Schema\Schema;
use App\Schema\Generator\JSONSchema\Diff\Schema\SchemaVersion;
use MinVWS\Codable\Encoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

use function array_keys;
use function count;
use function is_null;

#[Group('schema-jsonschema-diff')]
class SchemaDiffTest extends UnitTestCase
{
    public static function unmodifiedProvider(): array
    {
        $property1 = new Property('prop1', null, PropertyType::type('string'), null);
        $property2 = new Property('prop2', null, PropertyType::type('string'), null);

        $def1 = new EnumVersion(
            Descriptor::forEnumVersionDefKey('Enum-YesNoUnknown-V1'),
            null,
            null,
            [new EnumItem('value', 'description')],
        );

        $v1 = new SchemaVersion(Descriptor::forSchemaVersionDefKey('CovidCase-V1'), null, null, [$property1], []);
        $v2 = new SchemaVersion(Descriptor::forSchemaVersionDefKey('CovidCase-V3'), null, null, [$property1, $property2], [$def1]);

        return [
            'equal' => [
                new Schema('CovidCase', []),
                new Schema('CovidCase', []),
            ],
            'equal-with-single-version' => [
                new Schema('CovidCase', [$v1]),
                new Schema('CovidCase', [$v1]),
            ],
            'equal-with-multiple-versions' => [
                new Schema('CovidCase', [$v1, $v2]),
                new Schema('CovidCase', [$v1, $v2]),
            ],
        ];
    }

    #[DataProvider('unmodifiedProvider')]
    public function testDiffShouldReturnNullIfUnmodified(Schema $original, Schema $new): void
    {
        $this->assertNull($new->diff($original));
        $this->assertNull($original->diff($new));
    }

    public static function modifiedProvider(): array
    {
        $property1String = new Property('prop1', null, PropertyType::type('string'), null);
        $property1Int = new Property('prop1', null, PropertyType::type('int'), null);
        $property2 = new Property('prop2', null, PropertyType::type('string'), null);

        $def1 = new EnumVersion(
            Descriptor::forEnumVersionDefKey('Enum-YesNoUnknown-V1'),
            null,
            null,
            [new EnumItem('value', 'description')],
        );

        $v1 = new SchemaVersion(Descriptor::forSchemaVersionDefKey('CovidCase-V1'), null, null, [$property1String], []);
        $v1Modified = new SchemaVersion(Descriptor::forSchemaVersionDefKey('CovidCase-V1'), null, null, [$property1Int], []);
        $v2 = new SchemaVersion(Descriptor::forSchemaVersionDefKey('CovidCase-V2'), null, null, [$property1Int], []);
        $v3 = new SchemaVersion(Descriptor::forSchemaVersionDefKey('CovidCase-V3'), null, null, [$property1Int, $property2], [$def1]);

        return [
            'version-added' => [
                new Schema('CovidCase', []),
                new Schema('CovidCase', [$v1]),
                [
                    $v1->version => [
                        'diffType' => DiffType::Added,
                        'expectedPropertyDiffs' => false,
                        'expectedDefDiffs' => false,
                    ],
                ],
            ],
            'version-removed' => [
                new Schema('CovidCase', [$v1]),
                new Schema('CovidCase', []),
                [
                    $v1->version => [
                        'diffType' => DiffType::Removed,
                        'expectedPropertyDiffs' => false,
                        'expectedDefDiffs' => false,
                    ],
                ],
            ],
            'version-modified' => [
                new Schema('CovidCase', [$v1]),
                new Schema('CovidCase', [$v1Modified]),
                [
                    $v1->version => [
                        'diffType' => DiffType::Modified,
                        'expectedPropertyDiffs' => true,
                        'expectedDefDiffs' => false,
                    ],
                ],
            ],
            'version-added-compare-with-previous-version' => [
                new Schema('CovidCase', [$v2]),
                new Schema('CovidCase', [$v2, $v3]),
                [
                    $v3->version => [
                        'diffType' => DiffType::Added,
                        'expectedPropertyDiffs' => true,
                        'expectedDefDiffs' => true,
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('modifiedProvider')]
    public function testDiffShouldReturnModified(Schema $original, Schema $new, array $expectedVersionDiffs): void
    {
        $diff = $new->diff($original);
        $this->assertNotNull($diff);
        $this->assertEquals(DiffType::Modified, $diff->diffType);
        $this->assertSame($new, $diff->new);
        $this->assertSame($original, $diff->original);
        $this->assertCount(count($expectedVersionDiffs), $diff->versionDiffs);
        foreach ($expectedVersionDiffs as $version => $expectations) {
            $this->assertArrayHasKey($version, $diff->versionDiffs);
            $this->assertEquals($expectations['diffType'], $diff->versionDiffs[$version]->diffType);
            $this->assertEquals($expectations['expectedPropertyDiffs'], !is_null($diff->versionDiffs[$version]->propertyDiffs));
            $this->assertEquals($expectations['expectedDefDiffs'], !is_null($diff->versionDiffs[$version]->defDiffs));
        }
    }

    #[DataProvider('modifiedProvider')]
    public function testEncode(Schema $original, Schema $new, array $expectedVersionDiffs): void
    {
        $diff = $new->diff($original);
        $encoded = (new Encoder())->encode($diff);
        $this->assertEquals('modified', $encoded->diffType);
        $this->assertEquals($new->name ?? $original->name, $encoded->name);
        $this->assertEquals(count($expectedVersionDiffs), count($encoded->versionDiffs));
        $this->assertEqualsCanonicalizing(array_keys($expectedVersionDiffs), array_keys($encoded->versionDiffs));
    }
}
