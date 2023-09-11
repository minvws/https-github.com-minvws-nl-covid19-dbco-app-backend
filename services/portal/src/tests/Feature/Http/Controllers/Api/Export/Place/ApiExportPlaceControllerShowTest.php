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

use function sprintf;

#[Group('export')]
#[Group('export-place')]
class ApiExportPlaceControllerShowTest extends FeatureTestCase
{
    private Place $place;
    private ExportClient $client;
    private string $pseudoId;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = $this->createOrganisation();
        $stamp = CarbonImmutable::parse('1 minute ago');
        $this->place = $this->createPlaceForOrganisation($organisation, [
            'created_at' => $stamp,
            'updated_at' => $stamp,
        ]);
        $this->client = $this->createExportClient(
            purposes: [Purpose::EpidemiologicalSurveillance],
            organisations: [$organisation],
        );
        $encryptionHelper = $this->app->get(ExportPseudoIdHelper::class);
        $this->pseudoId = $encryptionHelper->idToPseudoIdForClient($this->place->uuid, $this->client);
    }

    public function testShowReturnsExpectedPlaceData(): void
    {
        $response = $this->be($this->client, 'export')
            ->getJson(sprintf('/api/export/places/%s/', $this->pseudoId));
        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('$schema'));
        $this->assertEquals($this->pseudoId, $response->json('pseudoId'));
    }

    public function testShowReturnsEmptyPlaceDataIfClientHasNoPurposes(): void
    {
        foreach ($this->client->purposes as $purpose) {
            $purpose->delete();
        }

        $this->client->refresh();

        $response = $this->be($this->client, 'export')
            ->getJson(sprintf('/api/export/places/%s/', $this->pseudoId));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('$schema', $data);
        $this->assertNotEmpty($data['$schema']);
    }

    public function testShowResultsIn404IfPlaceIsDeleted(): void
    {
        $this->place->delete();
        $response = $this->be($this->client, 'export')
            ->getJson(sprintf('/api/export/places/%s/', $this->pseudoId));
        $response->assertStatus(404);
    }
}
