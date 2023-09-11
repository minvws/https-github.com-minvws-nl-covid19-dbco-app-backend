<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Export\Case;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Export\ExportClient;
use App\Models\Export\ExportType;
use App\Models\Purpose\Purpose;
use App\Schema\Types\DateTimeType;
use App\Services\Export\ExportCaseService;
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
use function strtotime;
use function urlencode;

#[Group('export')]
#[Group('export-case')]
class ApiExportCaseControllerIndexMultipleCasesTest extends FeatureTestCase
{
    private EloquentOrganisation $organisation;

    private ExportClient $client;

    private EloquentCase $case1;
    private EloquentCase $case2;
    private EloquentCase $case3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = $this->createOrganisation();

        $this->client = $this->createExportClient(
            purposes: [Purpose::EpidemiologicalSurveillance],
            organisations: [$this->organisation],
        );

        $stamp1 = CarbonImmutable::parse('20 minute ago');
        $this->case1 = $this->createCaseForOrganisation($this->organisation, ['created_at' => $stamp1, 'updated_at' => $stamp1]);

        $stamp2 = CarbonImmutable::parse('45 minutes ago');
        $this->case2 = $this->createCaseForOrganisation($this->organisation, ['created_at' => $stamp2, 'updated_at' => $stamp2]);

        // outside of default since
        $stamp3 = CarbonImmutable::parse('3 hours ago');
        $this->case3 = $this->createCaseForOrganisation($this->organisation, ['created_at' => $stamp3, 'updated_at' => $stamp3]);
    }

    public function testReturnsMultipleCasesInOrder(): void
    {
        $pseudoIdHelper = $this->app->get(ExportPseudoIdHelper::class);

        $response = $this->be($this->client, 'export')->getJson(
            '/api/export/cases/?since=' . date(DateTimeType::FORMAT_DATETIME, strtotime('1 day ago')),
        );
        $response->assertStatus(200);
        $this->assertCount(3, $response->json('items'));
        $this->assertEquals($this->case3->uuid, $pseudoIdHelper->pseudoIdToIdForClient($response->json('items.0.pseudoId'), $this->client));
        $this->assertEquals($this->case2->uuid, $pseudoIdHelper->pseudoIdToIdForClient($response->json('items.1.pseudoId'), $this->client));
        $this->assertEquals($this->case1->uuid, $pseudoIdHelper->pseudoIdToIdForClient($response->json('items.2.pseudoId'), $this->client));
    }

    public function testReturnsMultipleCasesInOrderEvenIfOneHasBeenDeleted(): void
    {
        $this->case2->deleted_at = $this->case2->updatedAt;
        $this->case2->timestamps = false;
        $this->case2->save();
        $this->testReturnsMultipleCasesInOrder();
    }

    public function testCursorShouldBeBasedOnLastCase(): void
    {
        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/');
        $response->assertStatus(200);
        $this->assertArrayHasKey('cursor', $response->json());
        $cursorToken = $response->json('cursor');
        $cursorHelper = $this->app->get(ExportCursorHelper::class);
        $this->assertTrue($cursorHelper->isActiveCursorToken($cursorToken));
        $cursor = $cursorHelper->decodeCursorFromTokenForClient($cursorToken, ExportType::Case_, $this->client);
        $this->assertEquals($this->case1->uuid, $cursor->id);
        $this->assertTrue(CarbonImmutable::parse($this->case1->updatedAt)->equalTo($cursor->since));
    }

    public static function invalidDateProvider(): array
    {
        return [
            [''],
            ['invalid'],
            ['2020-01-01'],
            ['2030-01-01T00:00:00Z'],
        ];
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

        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/?since=' . $since);
        $response->assertStatus(200);
        $this->assertCount($expectedCount, $response->json('items'));
    }

    #[DataProvider('invalidDateProvider')]
    public function testSinceParameterIsInvalid(string $since): void
    {
        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/?since=' . $since);
        $response->assertStatus(422);
    }

    public function testSinceParameterProhibitedIfCursorGiven(): void
    {
        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/');
        $cursor = $response->json('cursor');
        $since = date(DateTimeType::FORMAT_DATETIME, strtotime('1 day ago'));
        $response = $this->be($this->client, 'export')->getJson(
            '/api/export/cases/?cursor=' . urlencode($cursor) . '&since=' . urlencode($since),
        );
        $response->assertStatus(422);
        $this->assertEquals('The cursor field prohibits since from being present.', $response->json('errors.cursor.0'));
    }

    public static function validUntilProvider(): array
    {
        return [
            ['now', 3],
            ['25 minutes ago', 2],
            ['50 minutes ago', 1],
            ['1 hour ago', 1],
            ['4 hours ago', 0],
        ];
    }

    #[DataProvider('validUntilProvider')]
    public function testRespectsUntilParameter(string $dateTimeString, int $expectedCount): void
    {
        $since = date(DateTimeType::FORMAT_DATETIME, strtotime('1 day ago'));
        $until = date(DateTimeType::FORMAT_DATETIME, strtotime($dateTimeString));

        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/?since=' . $since . '&until=' . $until);
        $response->assertStatus(200);
        $this->assertCount($expectedCount, $response->json('items'));
    }

    public static function invalidUntilProvider(): array
    {
        return [
            ['2020-01-01T00:00:00Z'],
        ];
    }

    #[DataProvider('invalidDateProvider')]
    #[DataProvider('invalidUntilProvider')]
    public function testUntilParameterIsInvalid(string $until): void
    {
        $since = date(DateTimeType::FORMAT_DATETIME, strtotime('1 day ago'));
        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/?since=' . $since . '&until=' . $until);
        $response->assertStatus(422);
    }

    public function testUntilParameterProhibitedIfCursorGiven(): void
    {
        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/');
        $cursor = $response->json('cursor');
        $until = date(DateTimeType::FORMAT_DATETIME, strtotime('20 minutes ago'));
        $response = $this->be($this->client, 'export')->getJson(
            '/api/export/cases/?cursor=' . urlencode($cursor) . '&until=' . urlencode($until),
        );
        $response->assertStatus(422);
        $this->assertEquals('The cursor field prohibits until from being present.', $response->json('errors.cursor.0'));
    }

    public function testCursorPagination(): void
    {
        $this->case1->forceDelete();
        $this->case2->forceDelete();
        $this->case3->forceDelete();

        $extraItemsAbovePageSize = 50;

        for ($i = 0; $i < ExportCaseService::PAGE_SIZE + $extraItemsAbovePageSize; $i++) {
            $stamp = CarbonImmutable::parse(rand(30, 35) . ' minutes ago');
            $this->createCaseForOrganisation($this->organisation, ['created_at' => $stamp, 'updated_at' => $stamp]);
        }

        $since = date(DateTimeType::FORMAT_DATETIME, strtotime('3 hours ago'));
        $until = date(DateTimeType::FORMAT_DATETIME, strtotime('25 minutes ago'));

        // create 1 case outside window
        $stamp = CarbonImmutable::parse('20 minutes ago');
        $this->createCaseForOrganisation($this->organisation, ['created_at' => $stamp, 'updated_at' => $stamp]);

        // first page should return page size items
        $response = $this->be($this->client, 'export')->getJson(
            '/api/export/cases/?since=' . urlencode($since) . '&until=' . urlencode($until),
        );
        $this->assertCount(ExportCaseService::PAGE_SIZE, $response->json('items'));
        $pseudoIdsFirstPage = array_map(static fn ($i) => $i['pseudoId'], $response->json('items'));
        $pseudoIdsFirstPage = array_unique($pseudoIdsFirstPage);
        $this->assertCount(ExportCaseService::PAGE_SIZE, $pseudoIdsFirstPage);
        $cursor = $response->json('cursor');

        // second page should return $extraItemsAbovePageSize items
        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/?cursor=' . urlencode($cursor));
        $this->assertCount($extraItemsAbovePageSize, $response->json('items'));
        $pseudoIdsSecondPage = array_map(static fn ($i) => $i['pseudoId'], $response->json('items'));
        $pseudoIdsSecondPage = array_unique($pseudoIdsSecondPage);
        $this->assertCount($extraItemsAbovePageSize, $pseudoIdsSecondPage);
        $this->assertCount(0, array_intersect($pseudoIdsFirstPage, $pseudoIdsSecondPage));
        $cursor2 = $response->json('cursor');

        // third page should return 0 items
        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/?cursor=' . urlencode($cursor2));
        $this->assertCount(0, $response->json('items'));
        $cursor3 = $response->json('cursor');

        // and the cursor returned by the third page should also return 0 items
        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/?cursor=' . urlencode($cursor3));
        $this->assertCount(0, $response->json('items'));
        $response->json('cursor');
    }

    public function testCursorIsBoundToClient(): void
    {
        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/');
        $response->assertStatus(200);
        $cursor = $response->json('cursor');

        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/?cursor=' . urlencode($cursor));
        $response->assertStatus(200);

        $anotherClient = $this->createExportClient(
            purposes: [Purpose::EpidemiologicalSurveillance],
            organisations: [$this->organisation],
        );
        $response = $this->be($anotherClient, 'export')->getJson('/api/export/cases/?cursor=' . urlencode($cursor));
        $response->assertStatus(403);
    }

    public function testCursorIsBoundToType(): void
    {
        $cursorHelper = $this->app->get(ExportCursorHelper::class);
        $cursor = $cursorHelper->createFirstPageCursor(CarbonImmutable::parse('1 day ago'));
        $cursorToken = $cursorHelper->encodeCursorToTokenForClient($cursor, ExportType::Place, $this->client);

        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/?cursor=' . urlencode($cursorToken));
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
        $response = $this->be($this->client, 'export')->getJson('/api/export/cases/?cursor=' . urlencode($cursorToken));
        $response->assertStatus(422);
        $this->assertEquals('Cursor is invalid or expired', $response->json('message'));
    }
}
