<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Export;

use App\Models\Event;
use App\Models\Export\ExportClient;
use App\Models\Export\ExportType;
use App\Models\Purpose\Purpose;
use App\Services\Export\Helpers\ExportCursorHelper;
use App\Services\Export\Helpers\ExportPseudoIdHelper;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function config;

#[Group('export')]
#[Group('export-event')]
class ApiExportEventControllerTest extends FeatureTestCase
{
    private ExportClient $client;
    private ExportPseudoIdHelper $pseudoIdHelper;
    private Event $event1;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('schema.classes', [Event::class]);

        $this->pseudoIdHelper = $this->app->get(ExportPseudoIdHelper::class);
        $organisation = $this->createOrganisation();
        $this->client = $this->createExportClient(
            purposes: Purpose::cases(),
            organisations: [$organisation],
        );

        $this->case = $this->createCaseForOrganisation($organisation);
        $stamp1 = CarbonImmutable::parse('20 minute ago');
        $this->event1 = $this->createEvent([
            'case_uuid' => $this->case->uuid,
            'created_at' => $stamp1,
            'updated_at' => $stamp1,
        ]);
        $stamp2 = CarbonImmutable::parse('45 minutes ago');
        $this->event2 = $this->createEvent([
            'case_uuid' => $this->case->uuid,
            'created_at' => $stamp2,
            'updated_at' => $stamp2,
        ]);
        $stamp3 = CarbonImmutable::parse('3 hours ago');
        $this->event3 = $this->createEvent([
            'case_uuid' => $this->case->uuid,
            'created_at' => $stamp3,
            'updated_at' => $stamp3,
        ]);
    }

    public function testReturnsMultipleEventsInOrder(): void
    {
        $cursorHelper = $this->app->get(ExportCursorHelper::class);
        $cursor = $cursorHelper->createFirstPageCursor(CarbonImmutable::parse('1 day ago'));
        $cursorToken = $cursorHelper->encodeCursorToTokenForClient($cursor, ExportType::Event, $this->client);

        $response = $this->be($this->client, 'export')->getJson('/api/export/events/?cursor=' . $cursorToken);
        $response->assertOk();

        $this->assertCount(3, $response->json('items'));
        $this->assertEquals(
            $this->event3->uuid,
            $this->pseudoIdHelper->pseudoIdToIdForClient($response->json('items.0.pseudoId'), $this->client),
        );
        $this->assertEquals(
            $this->event2->uuid,
            $this->pseudoIdHelper->pseudoIdToIdForClient($response->json('items.1.pseudoId'), $this->client),
        );
        $this->assertEquals(
            $this->event1->uuid,
            $this->pseudoIdHelper->pseudoIdToIdForClient($response->json('items.2.pseudoId'), $this->client),
        );
    }

    public function testItExportsEventDetails(): void
    {
        $pseudoId = $this->pseudoIdHelper->idToPseudoIdForClient($this->event1->uuid, $this->client);
        $response = $this->be($this->client, 'export')->getJson('/api/export/events/' . $pseudoId . '/');

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('$schema'));
        $this->assertEquals($pseudoId, $response->json('pseudoId'));
    }

    public function testItAbortsWhenCursorInvalid(): void
    {
        $response = $this->be($this->client, 'export')->getJson('/api/export/events/?cursor=aaaaa+123/');
        $response->assertStatus(422);
    }

    public function testItRespectsTheSinceParameter(): void
    {
        $response = $this->be($this->client, 'export')->getJson('/api/export/events/?since=2022-01-01T00:00:00Z');
        $response->assertStatus(200);
    }
}
