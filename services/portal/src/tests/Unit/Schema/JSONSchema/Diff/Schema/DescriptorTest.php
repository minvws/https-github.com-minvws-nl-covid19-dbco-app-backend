<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Schema\Descriptor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;
use ValueError;

#[Group('schema-jsonschema-diff')]
class DescriptorTest extends UnitTestCase
{
    public static function validSchemaVersionIdProvider(): array
    {
        return [
            ['/schemas/CovidCase/V1', 'CovidCase-V1', 'CovidCase', 1],
            ['/schemas/CovidCase/General/V1', 'CovidCase-General-V1', 'CovidCase-General', 1],
            ['https://example.org/schemas/CovidCase/V2', 'CovidCase-V2', 'CovidCase', 2],
            ['/schemas/json/schemas/CovidCase/V3', 'CovidCase-V3', 'CovidCase', 3],
        ];
    }

    #[DataProvider('validSchemaVersionIdProvider')]
    public function testParsingSchemaVersionId(string $id, string $expectedId, string $expectedName, int $expectedVersion): void
    {
        $desc = Descriptor::forSchemaVersionId($id);
        $this->assertEquals($expectedId, $desc->id);
        $this->assertEquals($expectedName, $desc->name);
        $this->assertEquals($expectedVersion, $desc->version);
    }

    public static function invalidSchemaVersionIdProvider(): array
    {
        return [
            ['/resources/CovidCase/V1'],
            ['https://example.org/CovidCase/General/V2'],
            ['CovidCase/V1'],
        ];
    }

    #[DataProvider('invalidSchemaVersionIdProvider')]
    public function testParsingInvalidSchemaVersionIdShouldResultInAnException(string $id): void
    {
        $this->expectException(ValueError::class);
        Descriptor::forSchemaVersionId($id);
    }

    public static function validSchemaVersionDefKeyProvider(): array
    {
        return [
            ['CovidCase-V1', 'CovidCase-V1', 'CovidCase', 1],
            ['CovidCase-General-V1', 'CovidCase-General-V1', 'CovidCase-General', 1],
        ];
    }

    #[DataProvider('validSchemaVersionDefKeyProvider')]
    public function testParsingSchemaVersionDefKey(string $defKey, string $expectedId, string $expectedName, int $expectedVersion): void
    {
        $desc = Descriptor::forSchemaVersionDefKey($defKey);
        $this->assertEquals($expectedId, $desc->id);
        $this->assertEquals($expectedName, $desc->name);
        $this->assertEquals($expectedVersion, $desc->version);
    }

    public static function invalidSchemaVersionDefKeyProvider(): array
    {
        return [
            ['CovidCase'],
            ['CovidCase-General'],
            ['CovidCase/V1'],
        ];
    }

    #[DataProvider('invalidSchemaVersionDefKeyProvider')]
    public function testParsingInvalidSchemaVersionDefKeyShouldResultInAnException(string $defKey): void
    {
        $this->expectException(ValueError::class);
        Descriptor::forSchemaVersionDefKey($defKey);
    }

    public static function validEnumVersionIdProvider(): array
    {
        return [
            ['/schemas/enums/YesNoUnknown/V1', 'Enum-YesNoUnknown-V1', 'Enum-YesNoUnknown', 1],
            ['https://example.org/schemas/enums/BCOPhase/V2', 'Enum-BCOPhase-V2', 'Enum-BCOPhase', 2],
            ['/schemas/json/schemas/enums/Status/V3', 'Enum-Status-V3', 'Enum-Status', 3],
        ];
    }

    #[DataProvider('validEnumVersionIdProvider')]
    public function testParsingEnumVersionId(string $id, string $expectedId, string $expectedName, int $expectedVersion): void
    {
        $desc = Descriptor::forEnumVersionId($id);
        $this->assertEquals($expectedId, $desc->id);
        $this->assertEquals($expectedName, $desc->name);
        $this->assertEquals($expectedVersion, $desc->version);
    }

    public static function invalidEnumVersionIdProvider(): array
    {
        return [
            ['/schemas/YesNoUnknown/V1'],
            ['https://example.org/schemas/BCOPhase/V2'],
            ['/schemas/json/schemas/enums/Status'],
            ['YesNoUnknown/V1'],
            ['Enum-YesNoUnknown-V1'],
        ];
    }

    #[DataProvider('invalidEnumVersionIdProvider')]
    public function testParsingInvalidEnumVersionIdShouldResultInAnException(string $id): void
    {
        $this->expectException(ValueError::class);
        Descriptor::forEnumVersionId($id);
    }

    public static function validEnumVersionDefKeyProvider(): array
    {
        return [
            ['Enum-YesNoUnknown-V1', 'Enum-YesNoUnknown-V1', 'Enum-YesNoUnknown', 1],
            ['Enum-BCOPhase-V2', 'Enum-BCOPhase-V2', 'Enum-BCOPhase', 2],
        ];
    }

    #[DataProvider('validEnumVersionDefKeyProvider')]
    public function testParsingEnumVersionDefKey(string $defKey, string $expectedId, string $expectedName, int $expectedVersion): void
    {
        $desc = Descriptor::forEnumVersionDefKey($defKey);
        $this->assertEquals($expectedId, $desc->id);
        $this->assertEquals($expectedName, $desc->name);
        $this->assertEquals($expectedVersion, $desc->version);
    }

    public static function invalidEnumVersionDefKeyProvider(): array
    {
        return [
            ['YesNoUnknown'],
            ['YesNoUnknownV1'],
            ['YesNoUnknown/V1'],
        ];
    }

    #[DataProvider('invalidEnumVersionDefKeyProvider')]
    public function testParsingInvalidEnumVersionDefKeyShouldResultInAnException(string $defKey): void
    {
        $this->expectException(ValueError::class);
        Descriptor::forEnumVersionDefKey($defKey);
    }

    #[DataProvider('validSchemaVersionIdProvider')]
    #[DataProvider('validEnumVersionIdProvider')]
    public function testParsingIdRef(string $id, string $expectedId, string $expectedName, int $expectedVersion): void
    {
        $desc = Descriptor::forRef($id);
        $this->assertEquals($expectedId, $desc->id);
        $this->assertEquals($expectedName, $desc->name);
        $this->assertEquals($expectedVersion, $desc->version);
    }

    #[DataProvider('validSchemaVersionDefKeyProvider')]
    #[DataProvider('validEnumVersionDefKeyProvider')]
    public function testParsingDefKeyRef(string $defKey, string $expectedId, string $expectedName, int $expectedVersion): void
    {
        $desc = Descriptor::forRef('#/$defs/' . $defKey);
        $this->assertEquals($expectedId, $desc->id);
        $this->assertEquals($expectedName, $desc->name);
        $this->assertEquals($expectedVersion, $desc->version);
    }

    #[DataProvider('invalidEnumVersionDefKeyProvider')]
    #[DataProvider('invalidSchemaVersionDefKeyProvider')]
    #[DataProvider('invalidSchemaVersionIdProvider')]
    public function testParsingInvalidRefShouldResultInAnException(string $ref): void
    {
        $this->expectException(ValueError::class);
        Descriptor::forRef($ref);
    }
}
