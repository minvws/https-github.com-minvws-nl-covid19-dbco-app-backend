<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Events\PolicyVersionCreated;
use App\Models\CovidCase\Symptoms;
use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;
use App\Models\Policy\CalendarView;
use App\Models\Policy\PolicyVersion;
use App\Models\Policy\RiskProfile;
use App\Repositories\Policy\PopulatorReferenceEnum;
use App\Services\CalendarDataCalculationService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\CalendarView as CalendarViewEnum;
use MinVWS\DBCO\Enum\Models\FixedCalendarItem;
use MinVWS\DBCO\Enum\Models\IndexRiskProfile;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\CreateFragments;

#[Group('policy')]
class CalendarDataCalculationServiceTest extends FeatureTestCase
{
    use CreateFragments;

    private CalendarDataCalculationService $calendarDataCalculationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calendarDataCalculationService = $this->app->make(CalendarDataCalculationService::class);
    }

    #[DataProvider('episodePeriodDataProvider')]
    public function testEpisodePeriod(string $dateOfSymptomOnsetString, string $expectedStartDateString, string $expectedEndDateString): void
    {
        Event::fake([PolicyVersionCreated::class]);

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2023-06-07'));
        $dateOfSymptomOnset = CarbonImmutable::parse($dateOfSymptomOnsetString);

        $case = $this->createCase([
            'test' => $this->createLatestEloquentCaseFragmentInstance(
                'test',
                static function (Test $test) use ($dateOfSymptomOnset): void {
                    $test->dateOfSymptomOnset = $dateOfSymptomOnset;
                    $test->dateOfTest = null;
                },
            ),
        ]);
        $case->refresh();

        $this->assertEquals(
            CarbonImmutable::parse($expectedStartDateString),
            $this->calendarDataCalculationService->episodePeriod($case->episode_start_date)->getStartDate(),
        );
        $this->assertEquals(
            CarbonImmutable::parse($expectedEndDateString),
            $this->calendarDataCalculationService->episodePeriod($case->episode_start_date)->getEndDate(),
        );
    }

    public static function episodePeriodDataProvider(): array
    {
        return [
            'Episode before current date' => [
                'dateOfSymptomOnsetString' => '2023-05-10',
                'expectedStartDateString' => '2023-04-24',
                'expectedEndDateString' => '2023-05-28',
            ],
            'Episode ending after current date' => [
                'dateOfSymptomOnsetString' => '2023-06-01',
                'expectedStartDateString' => '2023-05-15',
                'expectedEndDateString' => '2023-06-07',
            ],
        ];
    }

    public function testGetViews(): void
    {
        Event::fake([PolicyVersionCreated::class]);

        $case = $this->createCustomEloquentCase();

        $policyVersion = PolicyVersion::factory([
            'status' => PolicyVersionStatus::active(),
        ])->create();

        CalendarView::factory()
            ->recycle($policyVersion)
            ->withCalendarItems($policyVersion, [
                'fixed_calendar_item_enum' => $this->faker->randomElement(FixedCalendarItem::all()),
            ], 4)
            ->create([
                'calendar_view_enum' => CalendarViewEnum::indexSidebar(),
            ]);

        CalendarView::factory()
            ->recycle($policyVersion)
            ->withCalendarItems($policyVersion, [
                'fixed_calendar_item_enum' => $this->faker->randomElement(FixedCalendarItem::all()),
            ], 2)
            ->create([
                'calendar_view_enum' => CalendarViewEnum::indexTaskSourceSidebar(),
            ]);

        $views = $this->calendarDataCalculationService->getViews($case);
        $this->assertCount(2, $views);
        $this->assertCount(4, $views['index_sidebar']);
    }

    public function testGetViewsForCompleteddCaseWichIsMoreThan8WeeksOld(): void
    {
        Event::fake([PolicyVersionCreated::class]);

        $case = $this->createCase([
            'bco_status' => BCOStatus::completed(),
            'completed_at' => CarbonImmutable::parse('-10 weeks'),
        ]);

        $policyVersion = PolicyVersion::factory([
            'status' => PolicyVersionStatus::active(),
        ])->create();

        CalendarView::factory()
            ->recycle($policyVersion)
            ->withCalendarItems($policyVersion, [
                'fixed_calendar_item_enum' => $this->faker->randomElement(FixedCalendarItem::all()),
            ], 2)
            ->create([
                'calendar_view_enum' => CalendarViewEnum::indexTaskSourceSidebar(),
            ]);

        $this->assertCount(0, $this->calendarDataCalculationService->getViews($case));
    }

    public function testCalculateViewPeriods(): void
    {
        $case = $this->createCustomEloquentCase();
        $case->refresh(); //to set the episodeStartDate

        PolicyVersion::factory()->create();

        $periods = $this->calendarDataCalculationService->calculatePeriods($case);
        $this->assertCount(3, $periods);
    }

    public function testCalculateViewPoints(): void
    {
        Event::fake([PolicyVersionCreated::class]);

        $case = $this->createCase([
            'test' => $this->createLatestEloquentCaseFragmentInstance(
                'test',
                static function (Test $test): void {
                    $test->dateOfSymptomOnset = CarbonImmutable::parse('-3 days');
                    $test->dateOfTest = CarbonImmutable::parse(CarbonImmutable::yesterday());
                },
            ),
            'symptoms' => $this->createLatestEloquentCaseFragmentInstance(
                'symptoms',
                static function (Symptoms $symptoms): void {
                    $symptoms->hasSymptoms = YesNoUnknown::yes();
                },
            ),
            'bco_status' => BCOStatus::open(),
        ]);
        $case->refresh(); //to set the episodeStartDate

        $policyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::active(),
        ]);

        /** @var CalendarView $calendarView */
        CalendarView::factory()
            ->recycle($policyVersion)
            ->withCalendarItems($policyVersion, [
                'calendar_item_enum' => CalendarItemEnum::point(),
                'person_type_enum' => PolicyPersonType::index(),
                'populator_reference_enum' => $this->faker->randomElement(
                    [PopulatorReferenceEnum::DateOfTestIndex, PopulatorReferenceEnum::DateOfSymptomOnset],
                ),
            ], 2)
            ->create([
                'calendar_view_enum' => CalendarViewEnum::indexTaskSourceSidebar(),
            ]);

        $points = $this->calendarDataCalculationService->calculatePoints($case);
        $this->assertCount(2, $points);
    }

    public function testCalculateCalendarPeriods(): void
    {
        Event::fake([PolicyVersionCreated::class]);

        $case = $this->createCase([
            'test' => $this->createLatestEloquentCaseFragmentInstance(
                'test',
                static function (Test $test): void {
                    $test->dateOfSymptomOnset = CarbonImmutable::parse('-3 days');
                    $test->dateOfTest = CarbonImmutable::parse(CarbonImmutable::yesterday());
                },
            ),
            'symptoms' => $this->createLatestEloquentCaseFragmentInstance(
                'symptoms',
                static function (Symptoms $symptoms): void {
                    $symptoms->hasSymptoms = YesNoUnknown::yes();
                },
            ),
            'bco_status' => BCOStatus::open(),
        ]);
        $case->refresh(); //to set the episodeStartDate

        $policyVersion = PolicyVersion::factory()->create([
            'status' => PolicyVersionStatus::active(),
        ]);

        RiskProfile::factory()
            ->recycle($policyVersion)
            ->create([
                'person_type_enum' => PolicyPersonType::index(),
                'risk_profile_enum' => IndexRiskProfile::hasSymptoms(),
            ]);

        /** @var CalendarView $calendarView */
        CalendarView::factory()
            ->recycle($policyVersion)
            ->withCalendarItems($policyVersion, [
                'calendar_item_enum' => CalendarItemEnum::period(),
                'person_type_enum' => PolicyPersonType::index(),
                'populator_reference_enum' => $this->faker->randomElement(
                    [PopulatorReferenceEnum::SourcePeriod, PopulatorReferenceEnum::ContagiousPeriod],
                ),
            ], 2)
            ->create([
                'calendar_view_enum' => CalendarViewEnum::indexTaskSourceSidebar(),
            ]);

        // Create a CalendarView with a CalendarItem without a populator_reference_enum which would be a manually
        // added period as we are currently only able to calculate periods which have a populator_reference_enum.
        CalendarView::factory()
            ->recycle($policyVersion)
            ->withCalendarItems($policyVersion, [
                'person_type_enum' => PolicyPersonType::index(),
                'calendar_item_enum' => CalendarItemEnum::period(),
            ], 1)
            ->count(1)
            ->create([
                'calendar_view_enum' => CalendarViewEnum::indexTaskSourceTable(),
            ]);

        $periods = $this->calendarDataCalculationService->calculatePeriods($case);
        //We should receive 2 periods instead of the 3 periods which were created. The reason is that we created
        //a CalendarItem without a populator_reference_enum (which is currently the only way to select a
        //calculation). Therefore this Calendar item is ignored.
        $this->assertCount(2, $periods);
    }

    private function createCustomEloquentCase(): EloquentCase
    {
        return $this->createCase([
            'test' => $this->createLatestEloquentCaseFragmentInstance(
                'test',
                static function (Test $test): void {
                    $test->dateOfSymptomOnset = CarbonImmutable::parse(CarbonImmutable::yesterday());
                    $test->dateOfTest = null;
                },
            ),
            'symptoms' => $this->createLatestEloquentCaseFragmentInstance(
                'symptoms',
                static function (Symptoms $symptoms): void {
                    $symptoms->hasSymptoms = YesNoUnknown::yes();
                },
            ),
            'bco_status' => BCOStatus::open(),
        ]);
    }
}
