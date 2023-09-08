<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use App\Http\Controllers\Api\Export\SchemaLocationResolver;
use App\Models\CovidCase\General;
use App\Schema\Documentation\Documentation;
use App\Schema\Documentation\DocumentationProvider;
use App\Schema\Entity;
use App\Schema\Generator\JSONSchema\Config;
use App\Schema\Generator\JSONSchema\EnumVersionBuilder;
use App\Schema\Generator\JSONSchema\SchemaVersionBuilder;
use App\Schema\Generator\JSONSchema\UseCompoundSchemas;
use App\Schema\Generator\JSONSchemaGenerator;
use App\Schema\Purpose\PurposeSpecificationBuilder;
use App\Schema\Schema;
use App\Schema\Types\AnyType;
use App\Schema\Types\BoolType;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\FloatType;
use App\Schema\Types\IntType;
use App\Schema\Types\ObjectType;
use App\Schema\Types\StringType;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\TestCase;

use function assert;
use function base_path;
use function config;
use function escapeshellarg;
use function exec;
use function file_exists;
use function file_get_contents;
use function is_dir;
use function is_string;
use function json_decode;
use function json_last_error;
use function mkdir;
use function sprintf;
use function sys_get_temp_dir;

#[Group('schema')]
#[Group('schema-jsonschema')]
class JSONSchemaTest extends TestCase
{
    private ?DocumentationProvider $oldDocProvider;
    private Schema $schema1;
    private Schema $schema2;
    private ?string $tempSchemasPath = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempSchemasPath = sys_get_temp_dir() . '/schemas';
        if (!is_dir($this->tempSchemasPath)) {
            mkdir($this->tempSchemasPath);
        }

        $this->setUpDocumentationProvider();
        $this->setUpSchemas();
    }

    /**
     * Make sure we have documentation output in the generated JSON schemas.
     */
    private function setUpDocumentationProvider(): void
    {
        $this->oldDocProvider = Documentation::getProvider();

        Documentation::setProvider(new class implements DocumentationProvider {
            public function getDocumentation(string $identifier, string $key): ?string
            {
                return $identifier . '#' . $key;
            }
        });
    }

    private function setUpSchemas(): void
    {
        $schema1 = new Schema(Entity::class);
        $schema1->setCurrentVersion(1);
        $schema1->add(IntType::createField('int'));
        $schema1->add(StringType::createField('string'));
        $schema1->add(YesNoUnknown::getVersion(1)->createField('enum'))
            ->specifyPurpose(static function (PurposeSpecificationBuilder $builder): void {
                $builder->addPurpose(TestPurpose::PurposeA, TestSubPurpose::SubPurposeA);
            });
        $schema1->add(Symptom::getVersion(1)->createArrayField('array'));
        $this->schema1 = $schema1;

        $schema2 = new Schema(Entity::class);
        $schema2->setCurrentVersion(1);
        $schema2->add(FloatType::createField('float'));
        $schema2->add(BoolType::createField('bool'));
        $schema2->add(ObjectType::createField('object', stdClass::class));
        $schema2->add(AnyType::createField('anything'));
        $schema2->add(DateTimeType::createField('time', DateTimeType::FORMAT_TIME));
        $schema2->add(DateTimeType::createField('date', DateTimeType::FORMAT_DATE));
        $schema2->add(DateTimeType::createField('dateTime', DateTimeType::FORMAT_DATETIME));
        $schema2->add(DateTimeType::createField('year', 'Y'));
        $schema2->add($schema1->getVersion(1)->createField('schema'));
        $this->schema2 = $schema2;
    }

    protected function tearDown(): void
    {
        exec(sprintf("rm -rf %s", escapeshellarg($this->tempSchemasPath)));
        Documentation::setProvider($this->oldDocProvider);

        parent::tearDown();
    }

    private function createConfig(UseCompoundSchemas $useCompoundSchemas = UseCompoundSchemas::Internal): Config
    {
        $config = new Config(new SchemaLocationResolver($this->tempSchemasPath));
        $config->setUseCompoundSchemas($useCompoundSchemas);
        $config->setEncodingMode(EncodingContext::MODE_EXPORT);
        return $config;
    }

    public function testJSONSchemaForSchema(): void
    {
        $builder = new SchemaVersionBuilder($this->schema1->getVersion(1));
        $schema = $builder->build($this->createConfig());
        $this->assertIsArray($schema);
        $this->assertEquals('object', $schema['type']);
        $this->assertStringContainsString('#label', $schema['title']);
        $this->assertStringContainsString('#shortDescription', $schema['description']);
        $this->assertIsArray($schema['properties']);
        $this->assertTrue(isset($schema['properties']['int']));
        $this->assertEquals('integer', $schema['properties']['int']['type']);
        $this->assertStringContainsString('#shortDescription', $schema['properties']['int']['description']);

        $builder = new SchemaVersionBuilder($this->schema2->getVersion(1));
        $schema = $builder->build($this->createConfig());
        $this->assertIsArray($schema);
        $this->assertEquals('string', $schema['properties']['time']['type']);
        $this->assertEquals('time', $schema['properties']['time']['format']);
        $this->assertEquals('date', $schema['properties']['date']['format']);
        $this->assertEquals('date-time', $schema['properties']['dateTime']['format']);
        $this->assertFalse(isset($schema['properties']['year']['format']));
    }

    public function testPurpose(): void
    {
        $builder = new SchemaVersionBuilder($this->schema1->getVersion(1));
        $schema = $builder->build($this->createConfig());
        $this->assertIsArray($schema);

        // enum field has purpose spec
        $this->assertTrue(isset($schema['properties']['enum']['purposeSpecification']['purposes']));
        $this->assertTrue(isset($schema['properties']['enum']['purposeSpecification']['purposes']['a']));
        $this->assertFalse(isset($schema['properties']['enum']['purposeSpecification']['purposes']['b']));
        $this->assertEquals(
            TestPurpose::PurposeA->getLabel(),
            $schema['properties']['enum']['purposeSpecification']['purposes']['a']['description'],
        );
        $this->assertTrue(isset($schema['properties']['enum']['purposeSpecification']['purposes']['a']['subPurpose']));
        $this->assertEquals(
            TestSubPurpose::SubPurposeA->getLabel(),
            $schema['properties']['enum']['purposeSpecification']['purposes']['a']['subPurpose']['description'],
        );

        // string field does not
        $this->assertFalse(isset($schema['properties']['string']['purposes']));
    }

    public function testCompoundJSONSchema(): void
    {
        $builder = new SchemaVersionBuilder($this->schema1->getVersion(1));
        $schema = $builder->build($this->createConfig());
        $this->assertIsArray($schema);
        $this->assertTrue(isset($schema['$defs']));
        $this->assertTrue(isset($schema['properties']['enum']['$ref']));
        $this->assertFalse(isset($schema['properties']['enum']['oneOf']));
        $this->assertTrue(isset($schema['properties']['array']['items']['$ref']));
        $this->assertFalse(isset($schema['properties']['array']['items']['oneOf']));

        $builder = new SchemaVersionBuilder($this->schema2->getVersion(1));
        $schema = $builder->build($this->createConfig());
        $this->assertIsArray($schema);
        $this->assertTrue(isset($schema['$defs']));
        $this->assertTrue(isset($schema['properties']['schema']['$ref']));
        $this->assertFalse(isset($schema['properties']['schema']['properties']));
    }

    public function testNonCompoundJSONSchema(): void
    {
        $builder = new SchemaVersionBuilder($this->schema1->getVersion(1));
        $schema = $builder->build($this->createConfig(UseCompoundSchemas::No));
        $this->assertIsArray($schema);
        $this->assertFalse(isset($schema['$defs']));
        $this->assertTrue(isset($schema['properties']['enum']['$ref']));
        $this->assertTrue(isset($schema['properties']['array']['items']['$ref']));

        $builder = new SchemaVersionBuilder($this->schema2->getVersion(1));
        $schema = $builder->build($this->createConfig(UseCompoundSchemas::No));
        $this->assertIsArray($schema);
        $this->assertFalse(isset($schema['$defs']));
        $this->assertTrue(isset($schema['properties']['schema']['$ref']));
    }

    public function testJSONSchemaForEnum(): void
    {
        $builder = new EnumVersionBuilder(YesNoUnknown::getVersion(1));
        $schema = $builder->build($this->createConfig());
        $this->assertIsArray($schema);
        $this->assertArrayHasKey('oneOf', $schema);
        $this->assertIsArray($schema['oneOf']);
        $this->assertCount(3, $schema['oneOf']);
        foreach ($schema['oneOf'] as $item) {
            $this->assertCount(2, $item);
            $this->assertTrue(!empty($item['const']));
            $this->assertTrue(!empty($item['description']));
        }
    }

    public function testGenerator(): void
    {
        $generator = new JSONSchemaGenerator();

        $config = $this->createConfig();

        $path = $config->getPathForSchemaVersion($this->schema1->getVersion(1));
        $this->assertFalse(file_exists($path));
        $generator->generateForSchema($this->schema1, $config);
        $this->assertTrue(file_exists($path));
        $json = file_get_contents($path);
        $data = json_decode($json);
        $this->assertEquals(0, json_last_error());
        $this->assertIsObject($data);

        $path = $config->getPathForEnumVersion(YesNoUnknown::getVersion(1));
        $this->assertFalse(file_exists($path));
        $generator->generateForEnum(YesNoUnknown::class, $config);
        $this->assertTrue(file_exists($path));
        $json = file_get_contents($path);
        $data = json_decode($json);
        $this->assertEquals(0, json_last_error());
        $this->assertIsObject($data);
    }

    public function testSchemaLocationResolver(): void
    {
        $basePath = config('schema.output.json');
        assert(is_string($basePath));
        $locationResolver = new SchemaLocationResolver(base_path($basePath));
        $enumPath = $locationResolver->getPathForEnumVersion(YesNoUnknown::getVersion(1));
        $this->assertEquals(base_path('resources/schemas/json/enums/YesNoUnknown/V1.schema.json'), $enumPath);
        $schemaPath = $locationResolver->getPathForSchemaVersion(General::getSchema()->getVersion(1));
        $this->assertEquals(base_path('resources/schemas/json/CovidCase/General/V1.schema.json'), $schemaPath);
    }
}
