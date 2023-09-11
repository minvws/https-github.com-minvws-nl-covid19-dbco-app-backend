<?php

declare(strict_types=1);

namespace Tests\Feature\Prometheus;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Place;
use App\Models\Event;
use App\Models\Export\ExportClient;
use App\Models\Export\ExportType;
use App\Models\Metric\DataDisclosure\ExportRequests;
use App\Models\Metric\DataDisclosure\InvalidCertificate;
use App\Models\Metric\DataDisclosure\InvalidJwt;
use App\Models\Metric\DataDisclosure\StreamRequest;
use App\Models\Purpose\Purpose;
use App\Repositories\Metric\MetricRepository;
use App\Schema\Types\DateTimeType;
use App\Services\Export\Helpers\ExportCursorHelper;
use App\Services\Export\Helpers\ExportPseudoIdHelper;
use Carbon\CarbonImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function date;
use function strtotime;

#[Group('ExportApiCounters')]
class DataDisclosureCountersTest extends FeatureTestCase
{
    private ExportClient $client;
    private Event $event;
    private EloquentCase $case;
    private Place $place;
    private MockInterface $metricRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = $this->createOrganisation();
        $this->client = $this->createExportClient(
            purposes: Purpose::cases(),
            organisations: [$this->organisation],
        );

        $this->case = $this->createCaseForOrganisation($this->organisation);
        $this->place = $this->createPlaceForOrganisation($this->organisation);
        $this->event = $this->createEvent(['case_uuid' => $this->case->uuid]);

        $this->metricRepository = $this->spy(MetricRepository::class)->makePartial();
    }

    #[DataProvider('endpointsProvider')]
    public function testItCountsChangeFeedRequestsWithSinceParameterAndDuration(
        string $endpoint,
        ExportType $exportType,
    ): void {
        $response = $this->be($this->client, 'export')
            ->getJson($endpoint . '/?since=' . date(DateTimeType::FORMAT_DATETIME, strtotime('1 day ago')));
        $response->assertStatus(200);

        $this->metricRepository->shouldHaveReceived('measureCounter')
            ->with(Mockery::on(function ($metric) use ($exportType): bool {
                return ($metric instanceof StreamRequest) && $metric->getLabels() === [
                    'export_type' => $exportType->value,
                    'client_id' => (string) $this->client->id,
                    'api_parameter' => 'sinceParameter',
                ];
            }));
    }

    #[DataProvider('endpointsProvider')]
    public function testItCountsChangeFeedRequestsWithCursorParameterAndDuration(
        string $endpoint,
        ExportType $exportType,
    ): void {
        $cursorHelper = $this->app->get(ExportCursorHelper::class);
        $cursor = $cursorHelper->createFirstPageCursor(CarbonImmutable::parse('1 day ago'));
        $cursorToken = $cursorHelper->encodeCursorToTokenForClient($cursor, $exportType, $this->client);

        $response = $this->be($this->client, 'export')
            ->getJson($endpoint . '/?cursor=' . $cursorToken);
        $response->assertStatus(200);

        $this->metricRepository->shouldHaveReceived('measureCounter')
            ->with(Mockery::on(function ($metric) use ($exportType): bool {
                return ($metric instanceof StreamRequest) && $metric->getLabels() === [
                    'export_type' => $exportType->value,
                    'client_id' => (string) $this->client->id,
                    'api_parameter' => 'cursor',
                ];
            }));
    }

    #[DataProvider('endpointsProvider')]
    public function testItCountsInvalidCertificateOccurrences(string $endpoint): void
    {
        $response = $this->getJson($endpoint);
        $response->assertUnauthorized();

        $this->metricRepository->shouldHaveReceived('measureCounter')
            ->with(Mockery::on(static function ($metric): bool {
                return ($metric instanceof InvalidCertificate) && $metric->getLabels() === [];
            }));
    }

    #[DataProvider('endpointsProvider')]
    public function testItCountsInvalidJWTOccurrences(string $endpoint): void
    {
        $response = $this->be($this->client, 'export')->getJson($endpoint . '?cursor=clearlyAnInvalidToken');
        $response->assertStatus(422);

        $this->metricRepository->shouldHaveReceived('measureCounter')
            ->with(Mockery::on(function ($metric): bool {
                return ($metric instanceof InvalidJwt) && $metric->getLabels() === [
                    'client_id' => (string) $this->client->id,
                ];
            }));
    }

    #[DataProvider('endpointsProvider')]
    public function testItCountsExportRequestsWithDuration(string $endpoint, ExportType $type): void
    {
        $response = $this->be($this->client, 'export')
            ->getJson($endpoint . '/' . $this->getPseudonymizedIdForExportType($type));
        $response->assertStatus(200);

        $this->metricRepository->shouldHaveReceived('measureCounter')
            ->with(Mockery::on(function ($metric) use ($type): bool {
                return ($metric instanceof ExportRequests) && $metric->getLabels() === [
                    'export_type' => $type->value,
                    'client_id' => (string) $this->client->id,
                ];
            }));
    }

    public static function endpointsProvider(): array
    {
        return [
            'cases' => ['/api/export/cases', ExportType::Case_],
            'places' => ['/api/export/places', ExportType::Place],
            'events' => ['/api/export/events', ExportType::Event],
        ];
    }

    private function getPseudonymizedIdForExportType(ExportType $type): string
    {
        $uuidToPseudonymize = match ($type) {
            ExportType::Case_ => $this->case->uuid,
            ExportType::Place => $this->place->uuid,
            ExportType::Event => $this->event->uuid,
        };

        $encryptionHelper = $this->app->get(ExportPseudoIdHelper::class);
        return $encryptionHelper->idToPseudoIdForClient($uuidToPseudonymize, $this->client);
    }
}
