<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Model;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Schema\Descriptor;
use App\Schema\Generator\JSONSchema\Diff\Schema\Property;
use App\Schema\Generator\JSONSchema\Diff\Schema\PropertyType;
use App\Schema\Generator\JSONSchema\Diff\Schema\Schema;
use App\Schema\Generator\JSONSchema\Diff\Schema\SchemaList;
use App\Schema\Generator\JSONSchema\Diff\Schema\SchemaVersion;
use MinVWS\Codable\Encoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

use function array_keys;
use function count;

#[Group('schema-jsonschema-diff')]
class SchemaListDiffTest extends UnitTestCase
{
    public static function unmodifiedProvider(): array
    {
        $property1 = new Property('prop1', null, PropertyType::type('string'), null);
        $property2 = new Property('prop2', null, PropertyType::type('string'), null);

        $covidCaseV1 = new SchemaVersion(
            Descriptor::forSchemaVersionId('/schemas/json/schemas/CovidCase/V1'),
            null,
            null,
            [$property1],
            [],
        );
        $covidCaseV2 = new SchemaVersion(
            Descriptor::forSchemaVersionId('/schemas/json/schemas/CovidCase/V2'),
            null,
            null,
            [$property1, $property2],
            [],
        );
        $covidCaseSchema = new Schema('CovidCase', [$covidCaseV1, $covidCaseV2]);

        $eventV1 = new SchemaVersion(Descriptor::forSchemaVersionId('/schemas/json/schemas/Event/V1'), null, null, [$property1], []);
        $eventSchema = new Schema('Event', [$eventV1]);

        return [
            'equal' => [
                new SchemaList([]),
                new SchemaList([]),
            ],
            'equal-with-single-schema' => [
                new SchemaList([$covidCaseSchema]),
                new SchemaList([$covidCaseSchema]),
            ],
            'equal-with-multiple-schemas' => [
                new SchemaList([$covidCaseSchema, $eventSchema]),
                new SchemaList([$covidCaseSchema, $eventSchema]),
            ],
        ];
    }

    #[DataProvider('unmodifiedProvider')]
    public function testDiffShouldReturnNullIfUnmodified(SchemaList $original, SchemaList $new): void
    {
        $this->assertNull($new->diff($original));
        $this->assertNull($original->diff($new));
    }

    public static function modifiedProvider(): array
    {
        $property1 = new Property('prop1', null, PropertyType::type('string'), null);
        $property2 = new Property('prop2', null, PropertyType::type('string'), null);

        $covidCaseV1 = new SchemaVersion(
            Descriptor::forSchemaVersionId('/schemas/json/schemas/CovidCase/V1'),
            null,
            null,
            [$property1],
            [],
        );
        $covidCaseV1Modified = new SchemaVersion(
            Descriptor::forSchemaVersionId('/schemas/json/schemas/CovidCase/V1'),
            null,
            null,
            [$property2],
            [],
        );
        $covidCaseV2 = new SchemaVersion(
            Descriptor::forSchemaVersionId('/schemas/json/schemas/CovidCase/V2'),
            null,
            null,
            [$property1, $property2],
            [],
        );
        $covidCaseSchema = new Schema('CovidCase', [$covidCaseV1, $covidCaseV2]);
        $covidCaseSchemaModified = new Schema('CovidCase', [$covidCaseV1Modified, $covidCaseV2]);

        $eventV1 = new SchemaVersion(Descriptor::forSchemaVersionId('/schemas/json/schemas/Event/V1'), null, null, [$property1], []);
        $eventSchema = new Schema('Event', [$eventV1]);

        return [
            'schema-added' => [
                new SchemaList([]),
                new SchemaList([$covidCaseSchema]),
                [
                    $covidCaseSchema->name => DiffType::Added,
                ],
            ],
            'schema-removed' => [
                new SchemaList([$covidCaseSchema]),
                new SchemaList([]),
                [
                    $covidCaseSchema->name => DiffType::Removed,
                ],

            ],
            'schema-modified' => [
                new SchemaList([$covidCaseSchema]),
                new SchemaList([$covidCaseSchemaModified]),
                [
                    $covidCaseSchema->name => DiffType::Modified,
                ],
            ],
            'multiple-schema-added' => [
                new SchemaList([]),
                new SchemaList([$covidCaseSchema, $eventSchema]),
                [
                    $covidCaseSchema->name => DiffType::Added,
                    $eventSchema->name => DiffType::Added,
                ],
            ],
            'multiple-schema-removed' => [
                new SchemaList([$covidCaseSchema, $eventSchema]),
                new SchemaList([]),
                [
                    $covidCaseSchema->name => DiffType::Removed,
                    $eventSchema->name => DiffType::Removed,
                ],
            ],
            'mix' => [
                new SchemaList([$covidCaseSchema]),
                new SchemaList([$covidCaseSchemaModified, $eventSchema]),
                [
                    $covidCaseSchema->name => DiffType::Modified,
                    $eventSchema->name => DiffType::Added,
                ],
            ],
        ];
    }

    #[DataProvider('modifiedProvider')]
    public function testDiffShouldReturnModified(SchemaList $original, SchemaList $new, array $expectedSchemaDiffs): void
    {
        $diff = $new->diff($original);
        $this->assertNotNull($diff);
        $this->assertEquals(DiffType::Modified, $diff->diffType);
        $this->assertSame($new, $diff->new);
        $this->assertSame($original, $diff->original);
        $this->assertCount(count($expectedSchemaDiffs), $diff->schemaDiffs);
        foreach ($expectedSchemaDiffs as $name => $diffType) {
            $this->assertArrayHasKey($name, $diff->schemaDiffs);
            $this->assertEquals($diffType, $diff->schemaDiffs[$name]->diffType);
        }
    }

    #[DataProvider('modifiedProvider')]
    public function testEncode(SchemaList $original, SchemaList $new, array $expectedSchemaDiffs): void
    {
        $diff = $new->diff($original);
        $encoded = (new Encoder())->encode($diff);
        $this->assertEquals('modified', $encoded->diffType);
        $this->assertEquals(count($expectedSchemaDiffs), count($encoded->schemaDiffs));
        $this->assertEqualsCanonicalizing(array_keys($expectedSchemaDiffs), array_keys($encoded->schemaDiffs));
    }
}
