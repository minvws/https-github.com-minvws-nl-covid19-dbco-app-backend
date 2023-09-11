<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Schema\EnumVersion;
use App\Schema\Generator\JSONSchema\Diff\Schema\SchemaVersion;
use Generator;
use MinVWS\Codable\JSONDecoder;
use MinVWS\Codable\ValueNotFoundException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;
use Throwable;
use ValueError;

use function array_map;
use function count;
use function json_encode;

#[Group('schema-jsonschema-diff')]
class SchemaVersionTest extends UnitTestCase
{
    private static function createProperty(string $name): array
    {
        return [$name => ['type' => 'string']];
    }

    private static function createSchemaVersionDef(string $name): array
    {
        return [
            $name => [
                '$id' => $name,
                'type' => 'object',
                'properties' => [],
            ],
        ];
    }

    private static function createEnumVersionDef(string $name): array
    {
        return [
            $name => [
                '$id' => $name,
                'oneOf' => [],
            ],
        ];
    }

    public static function validProvider(): Generator
    {
        yield [
            [
                '$id' => 'schemas/json/schemas/CovidCase/V1',
                'properties' => [],
            ],
            'CovidCase',
            1,
            0,
            [],
        ];

        yield [
            [
                '$id' => 'schemas/json/schemas/Task/V4',
                'properties' => [],
                '$defs' => [],
            ],
            'Task',
            4,
            0,
            [],
        ];

        yield [
            [
                '$id' => 'schemas/json/schemas/CovidCase/V1',
                'properties' => [
                    ...self::createProperty('a'),
                    ...self::createProperty('b'),
                    ...self::createProperty('c'),
                ],
                '$defs' => [],
            ],
            'CovidCase',
            1,
            3,
            [],
        ];

        yield [
            [
                '$id' => '/schemas/json/schemas/CovidCase/V1',
                'properties' => [],
                '$defs' => [
                    ...self::createSchemaVersionDef('/schemas/json/schemas/CovidCase/General/V1'),
                    ...self::createSchemaVersionDef('/schemas/json/schemas/CovidCase/Abroad/V1'),
                    ...self::createEnumVersionDef('/schemas/json/schemas/enums/YesNoUnknown/V1'),
                ],
            ],
            'CovidCase',
            1,
            0,
            [
                'CovidCase-General-V1' => SchemaVersion::class,
                'CovidCase-Abroad-V1' => SchemaVersion::class,
                'Enum-YesNoUnknown-V1' => EnumVersion::class,
            ],
        ];
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('validProvider')]
    public function testDecode(array $data, string $name, int $version, int $propertyCount, array $defClasses): void
    {
        $json = json_encode($data);
        $schemaVersion = (new JSONDecoder())->decode($json)->decodeObject(SchemaVersion::class);
        $this->assertEquals($name, $schemaVersion->name);
        $this->assertEquals($version, $schemaVersion->version);
        $this->assertCount($propertyCount, $schemaVersion->properties);
        $this->assertCount(count($defClasses), $schemaVersion->defs);
        $this->assertEquals($defClasses, array_map(static fn ($d) => $d::class, $schemaVersion->defs));
    }

    public static function invalidProvider(): Generator
    {
        yield [
            [
                '$id' => 'CovidCase/V1',
                'properties' => [],
            ],
            ValueError::class,
        ];

        yield [
            [
                '$id' => 'schemas/json/schemas/Task/V4',
            ],
            ValueNotFoundException::class,
        ];
    }

    /**
     * @throws Throwable
     */
    #[DataProvider('invalidProvider')]
    public function testDecodeShouldThrowException(array $data, string $exceptionClass): void
    {
        $this->expectException($exceptionClass);
        $json = json_encode($data);
        (new JSONDecoder())->decode($json)->decodeObject(SchemaVersion::class);
    }
}
