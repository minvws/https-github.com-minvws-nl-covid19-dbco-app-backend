<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Export\Place;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\Place;
use App\Models\Export\ExportClient;
use App\Models\Export\ExportType;
use App\Models\Purpose\Purpose;
use App\Schema\Types\DateTimeType;
use App\Services\Export\ExportPlaceService;
use App\Services\Export\Helpers\ExportCursorHelper;
use App\Services\Export\Helpers\ExportPseudoIdHelper;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function array_intersect;
use function array_map;
use function array_unique;
use function date;
use function rand;
use function sprintf;
use function strtotime;
use function urlencode;

#[Group('export')]
#[Group('export-place')]
class ApiExportPlaceControllerIndexMultiplePlacesTest extends FeatureTestCase
{
    private EloquentOrganisation $organisation;

    private ExportClient $client;

    private Place $place1;
    private Place $place2;
    private Place $place3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = $this->createOrganisation();

        $this->client = $this->createExportClient(
            purposes: [Purpose::EpidemiologicalSurveillance],
            organisations: [$this->organisation],
        );

        $stamp1 = CarbonImmutable::parse('20 minute ago');
        $this->place1 = $this->createPlaceForOrganisation($this->organisation, [
            'created_at' => $stamp1,
            'updated_at' => $stamp1,
        ]);

        $stamp2 = CarbonImmutable::parse('45 minutes ago');
        $this->place2 = $this->createPlaceForOrganisation($this->organisation, [
            'created_at' => $stamp2,
            'updated_at' => $stamp2,
        ]);

        // outside of default since
        $stamp3 = CarbonImmutable::parse('3 hours ago');
        $this->place3 = $this->createPlaceForOrganisation($this->organisation, [
            'created_at' => $stamp3,
            'updated_at' => $stamp3,
        ]);
    }

    public function testReturnsMultiplePlacesInOrder(): void
    {
        $pseudoIdHelper = $this->app->get(ExportPseudoIdHelper::class);

        $response = $this->be($this->client, 'export')
            ->getJson(
                sprintf('/api/export/places/?since=%s', date(DateTimeType::FORMAT_DATETIME, strtotime('1 day ago'))),
            );
        $response->assertStatus(200);
        $this->assertCount(3, $response->json('items'));
        $this->assertEquals(
            $this->place3->uuid,
            $pseudoIdHelper->pseudoIdToIdForClient($response->json('items.0.pseudoId'), $this->client),
        );
        $this->assertEquals(
            $this->place2->uuid,
            $pseudoIdHelper->pseudoIdToIdForClient($response->json('items.1.pseudoId'), $this->client),
        );
        $this->assertEquals(
            $this->place1->uuid,
            $pseudoIdHelper->pseudoIdToIdForClient($response->json('items.2.pseudoId'), $this->client),
        );
    }

    public function testCursorShouldBeBasedOnLastPlace(): void
    {
        $response = $this->be($this->client, 'export')
            ->getJson('/api/export/places/');
        $response->assertStatus(200);
        $this->assertArrayHasKey('cursor', $response->json());
        $cursorToken = $response->json('cursor');
        $cursorHelper = $this->app->get(ExportCursorHelper::class);
        $this->assertTrue($cursorHelper->isActiveCursorToken($cursorToken));
        $cursor = $cursorHelper->decodeCursorFromTokenForClient($cursorToken, ExportType::Place, $this->client);
        $this->assertEquals($this->place1->uuid, $cursor->id);
        $this->assertTrue(CarbonImmutable::parse($this->place1->updatedAt)->equalTo($cursor->since));
    }

    public static function validSinceProvider(): array
    {
        return [
            ['now', 0],
            ['25 minutes ago', 1],
            ['50 minutes ago', 2],
            ['1 hour ago', 2],
            ['4 hours ago', 3],
            ['1 day ago', 3],
            ['1 year ago', 3],
        ];
    }

    #[DataProvider('validSinceProvider')]
    public function testRespectsSinceParameter(string $dateTimeString, int $expectedCount): void
    {
        $since = date(DateTimeType::FORMAT_DATETIME, strtotime($dateTimeString));

        $response = $this->be($this->client, 'export')
            ->getJson(sprintf('/api/export/places/?since=%s', $since));
        $response->assertStatus(200);
        $this->assertCount($expectedCount, $response->json('items'));
    }

    public static function invalidSinceProvider(): array
    {
        return [
            [''],
            ['invalid'],
            ['2020-01-01'],
            ['2030-01-01T00:00:00Z'],
        ];
    }

    #[DataProvider('invalidSinceProvider')]
    public function testSinceParameterIsInvalid(string $since): void
    {
        $response = $this->be($this->client, 'export')
            ->getJson(sprintf('/api/export/places/?since=%s', $since));
        $response->assertStatus(422);
    }

    public function testSinceParameterProhibitedIfCursorGiven(): void
    {
        $response = $this->be($this->client, 'export')
            ->getJson('/api/export/places/');
        $cursor = $response->json('cursor');
        $since = date(DateTimeType::FORMAT_DATETIME, strtotime('1 day ago'));

        $response = $this->be($this->client, 'export')
            ->getJson(sprintf('/api/export/places/?cursor=%s&since=%s', urlencode($cursor), urlencode($since)));
        $response->assertStatus(422);

        $this->assertEquals('The cursor field prohibits since from being present.', $response->json('errors.cursor.0'));
    }

    public function testCursorPagination(): void
    {
        $this->place1->forceDelete();
        $this->place2->forceDelete();
        $this->place3->forceDelete();

        $extraItemsAbovePageSize = 50;

        for ($i = 0; $i < ExportPlaceService::PAGE_SIZE + $extraItemsAbovePageSize; $i++) {
            $stamp = CarbonImmutable::parse(rand(20, 25) . ' minutes ago');
            $this->createPlaceForOrganisation($this->organisation, ['created_at' => $stamp, 'updated_at' => $stamp]);
        }

        $since = date(DateTimeType::FORMAT_DATETIME, strtotime('30 minutes ago'));
        $response = $this->be($this->client, 'export')
            ->getJson(sprintf('/api/export/places/?since=%s', urlencode($since)));
        $this->assertCount(ExportPlaceService::PAGE_SIZE, $response->json('items'));

        $pseudoIdsFirstPage = array_map(static fn ($i) => $i['pseudoId'], $response->json('items'));
        $pseudoIdsFirstPage = array_unique($pseudoIdsFirstPage);
        $this->assertCount(ExportPlaceService::PAGE_SIZE, $pseudoIdsFirstPage);

        $cursor = $response->json('cursor');
        $response = $this->be($this->client, 'export')
            ->getJson(sprintf('/api/export/places/?cursor=%s', urlencode($cursor)));
        $this->assertCount($extraItemsAbovePageSize, $response->json('items'));
        $pseudoIdsSecondPage = array_map(static fn ($i) => $i['pseudoId'], $response->json('items'));
        $pseudoIdsSecondPage = array_unique($pseudoIdsSecondPage);
        $this->assertCount($extraItemsAbovePageSize, $pseudoIdsSecondPage);

        $this->assertCount(0, array_intersect($pseudoIdsFirstPage, $pseudoIdsSecondPage));
    }

    public function testCursorIsBoundToClient(): void
    {
        $response = $this->be($this->client, 'export')
            ->getJson('/api/export/places/');
        $response->assertStatus(200);
        $cursor = $response->json('cursor');

        $response = $this->be($this->client, 'export')
            ->getJson(sprintf('/api/export/places/?cursor=%s', urlencode($cursor)));
        $response->assertStatus(200);

        $anotherClient = $this->createExportClient(
            purposes: [Purpose::EpidemiologicalSurveillance],
            organisations: [$this->organisation],
        );
        $response = $this->be($anotherClient, 'export')
            ->getJson(sprintf('/api/export/places/?cursor=%s', urlencode($cursor)));
        $response->assertStatus(403);
    }

    public function testCursorIsBoundToType(): void
    {
        $cursorHelper = $this->app->get(ExportCursorHelper::class);
        $cursor = $cursorHelper->createFirstPageCursor(CarbonImmutable::parse('1 day ago'));
        $cursorToken = $cursorHelper->encodeCursorToTokenForClient($cursor, ExportType::Case_, $this->client);

        $response = $this->be($this->client, 'export')
            ->getJson(sprintf('/api/export/places/?cursor=%s', urlencode($cursorToken)));
        $response->assertStatus(403);
    }

    public function testCursorShouldBeActive(): void
    {
        // create cursor in the past
        CarbonImmutable::setTestNow(CarbonImmutable::now()->subSeconds(ExportCursorHelper::EXPIRY_SECONDS + 1));
        $cursorHelper = $this->app->get(ExportCursorHelper::class);
        $cursor = $cursorHelper->createFirstPageCursor(CarbonImmutable::parse('1 hour ago'));
        $cursorToken = $cursorHelper->encodeCursorToTokenForClient($cursor, ExportType::Place, $this->client);

        // but in the current time it should be reported as expired
        CarbonImmutable::setTestNow();
        $response = $this->be($this->client, 'export')
            ->getJson(sprintf('/api/export/places/?cursor=%s', urlencode($cursorToken)));
        $response->assertStatus(422);
        $this->assertEquals('Cursor is invalid or expired', $response->json('message'));
    }
}
