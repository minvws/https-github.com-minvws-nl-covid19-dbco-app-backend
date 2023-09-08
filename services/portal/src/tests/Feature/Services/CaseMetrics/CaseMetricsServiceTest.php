<?php

declare(strict_types=1);

namespace Tests\Feature\Services\CaseMetrics;

use App\Helpers\Config;
use App\Models\Eloquent\CaseMetrics;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Services\CaseMetrics\CaseMetricsService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function array_column;
use function array_sum;
use function config;

class CaseMetricsServiceTest extends FeatureTestCase
{
    private CaseMetricsService $caseMetricsService;
    private EloquentOrganisation $organisation;
    private int $numDaysInPast;
    private CarbonInterface $periodEnd;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caseMetricsService = App::get(CaseMetricsService::class);
        $this->organisation = $this->createOrganisation();
        $this->numDaysInPast = 2;
        $this->periodEnd = CarbonImmutable::now()->floorDay();

        config()->set('casemetrics.created_archived_days_in_past', $this->numDaysInPast);
        config()->set('app.timezone', 'UTC');
    }

    public function testRefreshForOrganisationReturnsEmptyRowsWhenNoCases(): void
    {
        $this->caseMetricsService->refreshForOrganisation($this->organisation->uuid, $this->periodEnd);

        $metrics = $this->getCaseMetrics();

        $this->assertCount($this->numDaysInPast + 1, $metrics);
        $this->assertAllArchivedCountValuesInCaseMetrics($metrics, 0);
        $this->assertAllCreatedCountValuesInCaseMetrics($metrics, 0);
    }

    public function testRefreshForOrganisationCountsCreatedCasesWithinRange(): void
    {
        $randomCaseCount = $this->faker->numberBetween(1, 10);

        $dateInsideRange = CarbonImmutable::now()->subDays($this->faker->numberBetween(0, $this->numDaysInPast));
        EloquentCase::factory()
            ->count($randomCaseCount)
            ->create([
                'organisation_uuid' => $this->organisation->uuid,
                'created_at' => $dateInsideRange,
            ]);

        $this->caseMetricsService->refreshForOrganisation($this->organisation->uuid, $this->periodEnd);

        $metrics = $this->getCaseMetrics();

        $this->assertAllArchivedCountValuesInCaseMetrics($metrics, 0);
    }

    public function testRefreshForOrganisationOmitsCreatedCasesOutsideRange(): void
    {
        $dateOutsideRange = CarbonImmutable::now()->subDays($this->faker->numberBetween($this->numDaysInPast + 1, 100));
        $this->createCaseForOrganisation($this->organisation, ['created_at' => $dateOutsideRange]);

        $this->caseMetricsService->refreshForOrganisation($this->organisation->uuid, $this->periodEnd);

        $metrics = $this->getCaseMetrics();

        $this->assertAllCreatedCountValuesInCaseMetrics($metrics, 0);
    }

    public function testRefreshForOrganisationOmitsCreatedCasesOutsideOrganisation(): void
    {
        $now = CarbonImmutable::now();
        $this->createCaseForOrganisation($this->createOrganisation(), ['created_at' => $now]);
        $this->createCase([
            'assigned_organisation_uuid' => $this->organisation->uuid,
            'created_at' => $now,
        ]);

        $this->caseMetricsService->refreshForOrganisation($this->organisation->uuid, $this->periodEnd);

        $metrics = $this->getCaseMetrics();

        $this->assertAllCreatedCountValuesInCaseMetrics($metrics, 0);
    }

    public function testRefreshForOrganisationCountsArchivedCasesWithinRange(): void
    {
        $now = CarbonImmutable::now();
        $randomCaseCount = $this->faker->numberBetween(1, 10);

        for ($i = 0; $i < $randomCaseCount; $i++) {
            $updatedAt = $now->subDays($this->faker->numberBetween(0, $this->numDaysInPast));
            $case = $this->createCaseForOrganisation($this->organisation, [
                'bco_status' => BCOStatus::archived(),
                'updated_at' => $updatedAt,
            ]);
            $this->createCaseStatusHistoryWithStatusForCase(
                $case,
                BCOStatus::archived(),
                $updatedAt,
            );
        }

        $this->caseMetricsService->refreshForOrganisation($this->organisation->uuid, $this->periodEnd);

        $metrics = $this->getCaseMetrics();

        $this->assertEquals($randomCaseCount, array_sum(array_column($metrics->toArray(), 'archived_count')));
    }

    public function testRefreshForOrganisationOmitsArchivedCasesOutsideRange(): void
    {
        $dateOutsideRange = CarbonImmutable::now()->subDays($this->faker->numberBetween($this->numDaysInPast + 1, 100));
        $this->createCaseStatusHistoryWithStatusForCase(
            $this->createCaseForOrganisation($this->organisation, ['bco_status' => BCOStatus::archived()]),
            BCOStatus::archived(),
            $dateOutsideRange,
        );

        $this->caseMetricsService->refreshForOrganisation($this->organisation->uuid, $this->periodEnd);

        $metrics = $this->getCaseMetrics();

        $this->assertAllArchivedCountValuesInCaseMetrics($metrics, 0);
    }

    public function testRefreshForOrganisationOmitsArchivedCasesOutsideOrganisation(): void
    {
        $now = CarbonImmutable::now();
        $this->createCaseStatusHistoryWithStatusForCase(
            $this->createCaseForOrganisation($this->createOrganisation(), ['bco_status' => BCOStatus::archived()]),
            BCOStatus::archived(),
            $now,
        );
        $this->createCaseStatusHistoryWithStatusForCase(
            $this->createCase(['assigned_organisation_uuid' => $this->organisation->uuid, 'bco_status' => BCOStatus::archived()]),
            BCOStatus::archived(),
            $now,
        );

        $this->caseMetricsService->refreshForOrganisation($this->organisation->uuid, $this->periodEnd);

        $metrics = $this->getCaseMetrics();
        $this->assertAllArchivedCountValuesInCaseMetrics($metrics, 0);
    }

    public function testRefreshForOrganisationOmitsReopenedCases(): void
    {
        $date = CarbonImmutable::now()->subDays($this->faker->numberBetween(0, $this->numDaysInPast));
        $case = $this->createCaseForOrganisation($this->organisation, [
            'created_at' => $date,
            'updated_at' => $date,
            'bco_status' => BCOStatus::archived(),
        ]);
        $this->createCaseStatusHistoryWithStatusForCase($case, BCOStatus::archived(), $date);

        $this->caseMetricsService->refreshForOrganisation($this->organisation->uuid, $this->periodEnd);

        $metrics = $this->getCaseMetrics();
        $threeWeeksPlusToday = $this->numDaysInPast + 1;
        $this->assertCount($threeWeeksPlusToday, $metrics);
        $this->assertEquals(1, array_sum(array_column($metrics->toArray(), 'archived_count')));

        $case->refresh();
        $case->bco_status = BCOStatus::open();
        $case->save();

        $this->caseMetricsService->refreshForOrganisation($this->organisation->uuid, $this->periodEnd);

        $metrics = $this->getCaseMetrics();
        $this->assertAllArchivedCountValuesInCaseMetrics($metrics, 0);
    }

    /**
     * The definition of "previously archived cases" are cases archived before the `CaseStatusHistory` table existed.
     */
    public function testRefreshForOrganisationOmitsPreviouslyArchivedCases(): void
    {
        $now = CarbonImmutable::now();
        // Creating a case without a related `CaseStatusHistory` record
        $this->createCaseForOrganisation($this->organisation, [
            'created_at' => $now,
            'bco_status' => BCOStatus::archived(),
        ]);

        $this->caseMetricsService->refreshForOrganisation($this->organisation->uuid, $this->periodEnd);

        $metrics = $this->getCaseMetrics();
        $this->assertAllArchivedCountValuesInCaseMetrics($metrics, 0);
    }

    /**
     * @param string $timezone The timezone used for the metrics calculation
     * @param string $creationDate The covid case created_at timestamp in UTC
     * @param int $expectedValue Archived count included in the metrics
     */
    #[DataProvider('provideTimezoneData')]
    public function testRefreshForOrganisationWithCaseCreationTimezoneDifferences(
        string $timezone,
        string $creationDate,
        int $expectedValue,
    ): void {
        $date = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $creationDate, Config::string('app.timezone'));
        $case = $this->createCaseForOrganisation($this->organisation, [
            'created_at' => $date,
            'updated_at' => $date,
            'bco_status' => BCOStatus::archived(),
        ]);
        $this->createCaseStatusHistoryWithStatusForCase($case, BCOStatus::archived(), $date);

        $periodEnd = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $creationDate, $timezone)
            ->setTimezone(Config::string('app.timezone'))
            ->floorDay();
        $this->caseMetricsService->refreshForOrganisation($this->organisation->uuid, $periodEnd);
        $metrics = $this->getCaseMetrics();

        $this->assertAllArchivedCountValuesInCaseMetrics($metrics, $expectedValue);
        $this->assertAllCreatedCountValuesInCaseMetrics($metrics, $expectedValue);
    }

    public static function provideTimezoneData(): Generator
    {
        yield 'midway (-11), 0000' => [
            'Pacific/Midway',
            '2023-02-01 00:00:00',
            1,
        ];
        yield 'midway (-11), 0800' => [
            'Pacific/Midway',
            '2023-02-01 08:00:00',
            1,
        ];
        yield 'midway (-11), 2300' => [
            'Pacific/Midway',
            '2023-02-01 23:00:00',
            1,
        ];
        yield 'denver (-7), 0800' => [
            'America/Denver',
            '2023-02-01 08:00:00',
            1,
        ];
        yield 'amsterdam (+1), 0200' => [
            'Europe/Amsterdam',
            '2023-02-01 02:00:00',
            1,
        ];
        yield 'amsterdam (+1), 0800' => [
            'Europe/Amsterdam',
            '2023-02-01 08:00:00',
            1,
        ];
        yield 'tokyo (+9), 0800' => [
            'Asia/Tokyo',
            '2023-02-01 08:00:00',
            0,
        ];
        yield 'tokyo (+9), 0900' => [
            'Asia/Tokyo',
            '2023-02-01 09:00:00',
            1,
        ];
        yield 'krititimati (+14), 1300' => [
            'Pacific/Kiritimati',
            '2023-02-01 13:00:00',
            0,
        ];
        yield 'krititimati (+14), 1400' => [
            'Pacific/Kiritimati',
            '2023-02-01 14:00:00',
            1,
        ];
    }

    /**
     * @return Collection<int, CaseMetrics>
     */
    private function getCaseMetrics(): Collection
    {
        return CaseMetrics::query()
            ->where('organisation_uuid', '=', $this->organisation->uuid)
            ->get();
    }

    private function assertAllArchivedCountValuesInCaseMetrics(Collection $metrics, int $expectedSum): void
    {
        $actualSum = $metrics->sum(static function (CaseMetrics $metric): int {
            return $metric->archived_count;
        });

        $this->assertEquals($expectedSum, $actualSum);
    }

    private function assertAllCreatedCountValuesInCaseMetrics(Collection $metrics, int $expectedSum): void
    {
        $actualSum = $metrics->sum(static function (CaseMetrics $metric): int {
            return $metric->created_count;
        });

        $this->assertEquals($expectedSum, $actualSum);
    }
}
