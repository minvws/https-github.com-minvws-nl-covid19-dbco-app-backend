<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Export\Place;

use App\Models\Eloquent\Place;
use App\Models\Export\ExportClient;
use App\Models\Purpose\Purpose;
use App\Services\Export\Helpers\ExportPseudoIdHelper;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_keys;
use function route;

#[Group('export')]
#[Group('export-place')]
class ApiExportPlaceControllerIndexSinglePlaceTest extends FeatureTestCase
{
    private ExportClient $client;
    private Place $place;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = $this->createOrganisation();
        $this->client = $this->createExportClient(
            purposes: [Purpose::EpidemiologicalSurveillance],
            organisations: [$organisation],
        );
        $stamp = CarbonImmutable::parse('30 minutes ago');
        $this->place = $this->createPlaceForOrganisation($organisation, [
            'created_at' => $stamp,
            'updated_at' => $stamp,
        ]);
    }

    public function testReturnsFixedSetOfData(): void
    {
        $response = $this->be($this->client, 'export')
            ->getJson('/api/export/places/');
        $response->assertStatus(200);

        $item = $response->json('items.0');
        $this->assertEqualsCanonicalizing(['pseudoId', 'path', 'mutatedAt'], array_keys($item));
        $this->assertEquals(route('api-export-place', ['pseudoId' => $item['pseudoId']], false), $item['path']);
    }

    public function testPseudoIdIsForClient(): void
    {
        $response = $this->be($this->client, 'export')
            ->getJson('/api/export/places/');
        $response->assertStatus(200);

        $pseudoIdHelper = $this->app->get(ExportPseudoIdHelper::class);
        $uuid = $pseudoIdHelper->pseudoIdToIdForClient($response->json('items.0.pseudoId'), $this->client);
        $this->assertEquals($this->place->uuid, $uuid);
    }
}
