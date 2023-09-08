<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Export;

use App\Services\Export\JSONSchemaService;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('schema')]
#[Group('schema-jsonschema')]
#[Group('export')]
class ApiSchemaControllerTest extends FeatureTestCase
{
    public function testShow(): void
    {
        $stub = $this->createMock(JSONSchemaService::class);
        $stub->method('getJSONSchemaForPath')->willReturn('{"$id": "CovidCase"}');
        $this->app->singleton(JSONSchemaService::class, static fn() => $stub);
        $response = $this->be($this->createExportClient(), 'export')->getJson('/api/export/schemas/CovidCase/V1');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/schema+json');
        $response->assertJson(['$id' => 'CovidCase']);
    }

    public function testShow404(): void
    {
        $stub = $this->createMock(JSONSchemaService::class);
        $stub->method('getJSONSchemaForPath')->willReturn(null);
        $this->app->singleton(JSONSchemaService::class, static fn() => $stub);
        $response = $this->be($this->createExportClient(), 'export')->getJson('/api/export/schemas/CovidCase/V1');
        $response->assertStatus(404);
    }
}
