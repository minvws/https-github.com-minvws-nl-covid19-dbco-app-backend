<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Export;

use App\Http\Controllers\Api\Export\SchemaLocationResolver;
use App\Schema\Generator\JSONSchema\Config;
use App\Schema\Generator\JSONSchema\UseCompoundSchemas;
use App\Schema\Generator\JSONSchemaGenerator;
use App\Services\Catalog\EnumTypeRepository;
use App\Services\Export\JSONSchemaService;
use Illuminate\Config\Repository;
use MinVWS\Codable\EncodingContext;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\Feature\FeatureTestCase;

use function app;
use function escapeshellarg;
use function exec;
use function is_dir;
use function is_string;
use function iterator_to_array;
use function mkdir;
use function sprintf;
use function sys_get_temp_dir;

#[Group('schema')]
#[Group('schema-jsonschema')]
#[Group('export')]
class JSONSchemaServiceTest extends FeatureTestCase
{
    private ?string $tempSchemasPath = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempSchemasPath = sys_get_temp_dir() . '/schemas';
        if (!is_dir($this->tempSchemasPath)) {
            mkdir($this->tempSchemasPath);
        }
    }

    protected function tearDown(): void
    {
        exec(sprintf("rm -rf %s", escapeshellarg($this->tempSchemasPath)));

        parent::tearDown();
    }

    private function createConfig(UseCompoundSchemas $useCompoundSchemas = UseCompoundSchemas::External): Config
    {
        $config = new Config(new SchemaLocationResolver($this->tempSchemasPath));
        $config->setUseCompoundSchemas($useCompoundSchemas);
        $config->setEncodingMode(EncodingContext::MODE_EXPORT);
        return $config;
    }

    public function testGenerateJSONSchemasForSchemas(): void
    {
        $mock = $this->createMock(JSONSchemaGenerator::class);
        $mock->method('generateForSchema');
        $mock->expects($this->atLeastOnce())->method('generateForSchema');

        $this->app->singleton(JSONSchemaGenerator::class, static fn() => $mock);
        $jsonSchemaService = $this->app->get(JSONSchemaService::class);
        $result = $jsonSchemaService->generateJSONSchemasForSchemas($this->createConfig());

        foreach ($result as [$step, $maxSteps, $class]) {
            $this->assertIsInt($step);
            $this->assertIsInt($maxSteps);
            $this->assertTrue($class === null || is_string($class));
        }
    }

    public function testGenerateJSONSchemasForEnums(): void
    {
        $repository = new EnumTypeRepository(
            __DIR__ . '/../../Http/Controllers/Api/Dummy/Enums/index.json',
            'Tests\\Feature\\Http\\Controllers\\Api\\Dummy\\',
        );
        app()->instance(EnumTypeRepository::class, $repository);

        $mock = $this->createMock(JSONSchemaGenerator::class);
        $mock->method('generateForEnum');
        $mock->expects($this->atLeastOnce())->method('generateForEnum');

        $this->app->singleton(JSONSchemaGenerator::class, static fn() => $mock);
        $jsonSchemaService = $this->app->get(JSONSchemaService::class);
        $result = $jsonSchemaService->generateJSONSchemasForEnums($this->createConfig(UseCompoundSchemas::No));

        foreach ($result as [$step, $maxSteps, $class]) {
            $this->assertIsInt($step);
            $this->assertIsInt($maxSteps);
            $this->assertTrue($class === null || is_string($class));
        }
    }

    public function testGenerateJSONSchemasForEnumsDoesNothingWhenUsingCompoundSchemas(): void
    {
        $mock = $this->createMock(JSONSchemaGenerator::class);
        $mock->method('generateForEnum');
        $mock->expects($this->never())->method('generateForEnum');

        $this->app->singleton(JSONSchemaGenerator::class, static fn() => $mock);
        $jsonSchemaService = $this->app->get(JSONSchemaService::class);
        $result = $jsonSchemaService->generateJSONSchemasForEnums($this->createConfig());
        iterator_to_array($result); // trigger processing schema classes
    }

    public function testInvalidSchemaClass(): void
    {
        $config = $this->app->make(Repository::class);
        $config->set('schema.classes', [stdClass::class]);
        $jsonSchemaService = $this->app->get(JSONSchemaService::class);
        $this->expectExceptionMessage('stdClass does not implement App\Schema\SchemaProvider interface!');
        $result = $jsonSchemaService->generateJSONSchemasForSchemas($this->createConfig(UseCompoundSchemas::No));
        iterator_to_array($result); // trigger processing schema classes
    }
}
