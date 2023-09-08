<?php

declare(strict_types=1);

namespace Tests\Unit\Schema\JSONSchema;

use App\Schema\Generator\JSONSchema\Config;
use App\Schema\Generator\JSONSchema\LocationResolver;
use App\Schema\Generator\JSONSchema\SchemaVersionBuilder;
use App\Schema\Generator\JSONSchema\UseCompoundSchemas;
use App\Schema\Schema;
use App\Schema\SchemaVersion;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\EnumVersion;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\TestCase;

use function explode;
use function last;
use function str_replace;

#[Group('schema')]
#[Group('schema-jsonschema')]
class SchemaVersionBuilderTest extends TestCase
{
    private function createLocationResolver(): LocationResolver
    {
        return new class implements LocationResolver {
            public function getPathForSchemaVersion(SchemaVersion $schemaVersion): string
            {
                return str_replace('.', '/', $schemaVersion->getDocumentationIdentifier()) . '/' . $schemaVersion->getVersion();
            }

            public function getUrlForSchemaVersion(SchemaVersion $schemaVersion): string
            {
                return '/' . $this->getPathForSchemaVersion($schemaVersion);
            }

            public function getPathForEnumVersion(EnumVersion $enumVersion): string
            {
                $shortName = last(explode('\\', $enumVersion->getEnumClass()));
                return 'enums/' . $shortName . '/V' . $enumVersion->getVersion();
            }

            public function getUrlForEnumVersion(EnumVersion $enumVersion): string
            {
                return '/' . $this->getPathForEnumVersion($enumVersion);
            }
        };
    }

    private function createConfig(LocationResolver $locationResolver, UseCompoundSchemas $useCompoundSchemas): Config
    {
        $config = new Config($locationResolver);
        $config->setUseCompoundSchemas($useCompoundSchemas);
        $config->setEncodingMode(EncodingContext::MODE_EXPORT);
        return $config;
    }

    private function createSchema(): Schema
    {
        $schema = new Schema(stdClass::class);
        $schema->add(YesNoUnknown::getCurrentVersion()->createField('enum'));
        return $schema;
    }

    public static function useCompoundSchemasProvider(): array
    {
        return [
            'no' => [UseCompoundSchemas::No, '/enums/YesNoUnknown/V1', null],
            'external' => [UseCompoundSchemas::External, '/enums/YesNoUnknown/V1', 'Enum-YesNoUnknown-V1'],
            'internal' => [UseCompoundSchemas::Internal, '#/$defs/Enum-YesNoUnknown-V1', 'Enum-YesNoUnknown-V1'],
        ];
    }

    #[DataProvider('useCompoundSchemasProvider')]
    public function testUseCompoundSchemas(UseCompoundSchemas $useCompoundSchemas, string $expectedRef, ?string $expectedDef): void
    {
        $locationResolver = $this->createLocationResolver();
        $config = $this->createConfig($locationResolver, $useCompoundSchemas);
        $schema = $this->createSchema();
        $builder = new SchemaVersionBuilder($schema->getCurrentVersion());
        $result = $builder->build($config);
        $this->assertEquals($expectedRef, $result['properties']['enum']['$ref']);
        $this->assertEquals($expectedDef !== null, isset($result['$defs']));
        if ($expectedDef !== null) {
            $this->assertArrayHasKey('$defs', $result);
        }
    }
}
