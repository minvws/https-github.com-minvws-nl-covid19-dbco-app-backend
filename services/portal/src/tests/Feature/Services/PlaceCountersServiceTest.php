<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Helpers\Config;
use App\Services\Place\PlaceCountersService;
use Carbon\CarbonImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

use function array_merge;

class PlaceCountersServiceTest extends FeatureTestCase
{
    protected PlaceCountersService $placeCountersService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->placeCountersService = $this->app->get(PlaceCountersService::class);
    }

    public function testCanSyncCountersForPlaceWhichDoesNotHaveARecordYet(): void
    {
        $place = $this->createPlace([
            'index_count_reset_at' => $indexCountResetAt = $this->faker->dateTime(),
        ]);
        $case = $this->createCase([
            'created_at' => CarbonImmutable::now(),
            'date_of_test' => CarbonImmutable::now(),
            'date_of_symptom_onset' => CarbonImmutable::now(),
        ]);
        $this->createContextForCase($case, [
            'place_uuid' => $place,
            'place_added_at' => $this->faker->dateTimeBetween($indexCountResetAt),
        ]);

        $this->placeCountersService->calculateValuesForPlace($place);

        $this->assertDatabaseHas('place_counters', [
            'place_uuid' => $place->uuid,
            'index_count' => 1,
            'index_count_since_reset' => 1,
        ]);
    }

    public function testCanSyncCountersForPlaceWhichDoesHaveARecord(): void
    {
        $place = $this->createPlace([
            'index_count_reset_at' => $indexCountResetAt = $this->faker->dateTime(),
        ]);
        $this->createPlaceCountersForPlace($place);
        $case = $this->createCase([
            'created_at' => CarbonImmutable::now(),
            'date_of_test' => CarbonImmutable::now(),
            'date_of_symptom_onset' => CarbonImmutable::now(),
        ]);
        $this->createContextForCase($case, [
            'place_uuid' => $place,
            'place_added_at' => $this->faker->dateTimeBetween($indexCountResetAt),
        ]);

        $this->placeCountersService->calculateValuesForPlace($place);

        $this->assertDatabaseHas('place_counters', [
            'place_uuid' => $place->uuid,
            'index_count' => 1,
            'index_count_since_reset' => 1,
        ]);
    }

    public static function placeCountersDataProvider(): Generator
    {
        yield "bare minimum input" => [
            [
                'place_added_at' => CarbonImmutable::now(),
            ],
            [
                'index_count' => 1,
                'index_count_since_reset' => 1,
            ],
        ];

        yield "null on `place_added_at` should not count" => [
            [
                'place_added_at' => null,
            ],
            [
                'index_count' => 1,
                'index_count_since_reset' => 0,
            ],
        ];
    }

    #[DataProvider('placeCountersDataProvider')]
    public function testPlaceCountersUpdate(
        array $contextAttributes = [],
        array $expectedRecords = [],
    ): void {
        $place = $this->createPlace([
            'index_count_reset_at' => CarbonImmutable::now()->subDay(),
        ]);

        $case = $this->createCase([
            'date_of_test' => CarbonImmutable::now(),
        ]);

        $this->createContextForCase($case, array_merge($contextAttributes, [
            'place_uuid' => $place->uuid,
        ]));

        $this->placeCountersService->calculateValuesForPlace($place);

        $this->assertDatabaseHas('place_counters', array_merge($expectedRecords, [
            'place_uuid' => $place->uuid,
        ]));
    }

    public function testPlaceCountersUpdatedAtWillNotChangeIfThereArentAny(): void
    {
        CarbonImmutable::setTestNow($dateTime = $this->faker->dateTime);

        $place = $this->createPlace();

        $this->createPlaceCountersForPlace($place, [
            'updated_at' => $dateTime,
        ]);

        $this->placeCountersService->calculateValuesForPlace($place);

        $this->assertDatabaseHas('place_counters', [
            'updated_at' => $dateTime,
            'place_uuid' => $place->uuid,
        ]);
    }

    public function testPlaceCountersWithMultipleRecords(): void
    {
        $place = $this->createPlace([
            'index_count_reset_at' => $indexCountResetAt = $this->faker->dateTime(),
        ]);

        $case1 = $this->createCase([
            'created_at' => CarbonImmutable::now(),
            'date_of_test' => CarbonImmutable::now(),
            'date_of_symptom_onset' => CarbonImmutable::now(),
        ]);

        $this->createContextForCase($case1, [
            'place_uuid' => $place->uuid,
            'place_added_at' => $this->faker->dateTimeBetween($indexCountResetAt),
        ]);

        $case2 = $this->createCase([
            'created_at' => CarbonImmutable::now(),
            'date_of_test' => CarbonImmutable::now(),
            'date_of_symptom_onset' => CarbonImmutable::now(),
        ]);

        $this->createContextForCase($case2, [
            'place_uuid' => $place->uuid,
            'place_added_at' => $this->faker->dateTimeBetween($indexCountResetAt),
        ]);

        $this->placeCountersService->calculateValuesForPlace($place);

        $this->assertDatabaseHas('place_counters', [
            'place_uuid' => $place->uuid,
            'index_count' => 2,
            'index_count_since_reset' => 2,
        ]);
    }

    public function testNoLastIndexPresenceWhenContextHasNoMoments(): void
    {
        // Given a CovidCase
        $case = $this->createCase();

        // And a Place
        $place = $this->createPlace();

        // And a Context associated with this Place and that CovidCase
        $this->createContextForCase($case, ['place_uuid' => $place->uuid]);

        // Then the Place has no last index presence
        $place->refresh();
        self::assertNull($place->placeCounters->last_index_presence);
    }

    public function testLastIndexPresenceUpdatedWhenContextHasMoment(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::now());

        // Given a CovidCase
        $case = $this->createCase();

        // And a Place
        $place = $this->createPlace();

        // And a Context associated with this Place and that CovidCase
        $context = $this->createContextForCase($case, ['place_uuid' => $place->uuid]);

        // When a Moment some days ago is added to that Context
        $daysAgo = $this->faker->randomNumber(1);
        $day = CarbonImmutable::now()->subDays($daysAgo)->startOfDay();
        $this->createMomentForContext($context, ['day' => $day]);

        // Then the last index presence at the Place is so many days ago
        $place->refresh();
        self::assertEquals($day, $place->placeCounters->last_index_presence);
    }

    public function testLastIndexPresenceUpdatedWhenMomentIsRemovedFromContext(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::now());

        // Given a CovidCase
        $case = $this->createCase([
            'episode_start_date' => CarbonImmutable::now(),
            'created_at' => CarbonImmutable::now(),
        ]);

        // And a Place
        $place = $this->createPlace();

        // And a Context associated with this Place and that CovidCase
        $context = $this->createContextForCase($case, ['place_uuid' => $place->uuid]);

        // And this context has a Moment some days ago
        $daysAgo = $this->faker->randomNumber(1);
        $day = CarbonImmutable::now()->subDays($daysAgo)->startOfDay();
        $this->createMomentForContext($context, ['day' => $day]);

        // And this context has another Moment one day before some days ago
        $oneMoreDayAgo = $daysAgo - 1;
        $anotherDay = CarbonImmutable::now()->subDays($oneMoreDayAgo)->startOfDay();
        $moment = $this->createMomentForContext($context, ['day' => $anotherDay]);

        // When the oldest Moment is removed from the Context
        $moment->delete();

        // Then the last index presence at the Place is some days ago
        $place->refresh();
        self::assertEquals($day, $place->placeCounters->last_index_presence);
    }

    public function testPlaceCountersUpdatedWhenContextIsRemovedFromPlace(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::now());

        // Given a CovidCase
        $case = $this->createCase([
            'episode_start_date' => CarbonImmutable::now(),
            'created_at' => CarbonImmutable::now(),
        ]);

        // And a Place
        $place = $this->createPlace();

        // And a Context associated with this Place and that CovidCase
        $context = $this->createContextForCase($case, ['place_uuid' => $place->uuid]);

        // And this context has a Moment some days ago
        $daysAgo = $this->faker->randomNumber(1);
        $day = CarbonImmutable::now()->subDays($daysAgo)->startOfDay();
        $this->createMomentForContext($context, ['day' => $day]);

        // And another Context associated with this Place and that CovidCase
        $anotherContext = $this->createContextForCase($case, ['place_uuid' => $place->uuid]);

        // And this context has a Moment one day before some days ago
        $oneMoreDayAgo = $daysAgo - 1;
        $anotherDay = CarbonImmutable::now()->subDays($oneMoreDayAgo)->startOfDay();
        $this->createMomentForContext($anotherContext, ['day' => $anotherDay]);

        // When the first Context is deleted
        $context->delete();

        // Then the last index presence at the Place is one day before some days ago
        $place->refresh();
        self::assertEquals(1, $place->placeCounters->index_count);
        self::assertEquals(1, $place->placeCounters->index_count_since_reset);
        self::assertEquals($anotherDay, $place->placeCounters->last_index_presence);
    }

    public function testIndexCountUpdatedWhenContextIsCreated(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::now());

        // Given a CovidCase
        $case = $this->createCase([
            'episode_start_date' => CarbonImmutable::now(),
            'created_at' => CarbonImmutable::now(),
        ]);

        // And a Place
        $place = $this->createPlace();

        // When a Context is created for this Place and that CovidCase
        $this->createContextForCase($case, ['place_uuid' => $place->uuid]);

        // Then the index count at the Place is one
        $place->refresh();
        self::assertEquals(1, $place->placeCounters->index_count);
    }

    public function testIndexCountUpdatedWhenContextIsRemovedFromPlace(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::now());

        // Given a CovidCase
        $case = $this->createCase(['episode_start_date' => CarbonImmutable::now()]);

        // And a Place
        $place = $this->createPlace();

        // And a Context associated with this Place and that CovidCase
        $context = $this->createContextForCase($case, ['place_uuid' => $place->uuid]);

        // And this context has a Moment some days ago
        $daysAgo = $this->faker->randomNumber(1);
        $day = CarbonImmutable::now()->subDays($daysAgo)->startOfDay();
        $this->createMomentForContext($context, ['day' => $day]);

        // And another Context associated with this Place and that CovidCase
        $anotherContext = $this->createContextForCase($case, ['place_uuid' => $place->uuid]);

        // And this context has a Moment one day before some days ago
        $oneMoreDayAgo = $daysAgo - 1;
        $anotherDay = CarbonImmutable::now()->subDays($oneMoreDayAgo)->startOfDay();
        $this->createMomentForContext($anotherContext, ['day' => $anotherDay]);

        // When the first Context is deleted
        $context->delete();

        // Then the index count at the Place is one
        $place->refresh();
        self::assertEquals(1, $place->placeCounters->index_count);
    }

    public function testIndexCountUpdatedWhenCovidCaseIsUpdated(): void
    {
        // Given a CovidCase
        $case = $this->createCase(['date_of_symptom_onset' => CarbonImmutable::now()]);

        // And a Place
        $place = $this->createPlace();

        // And a Context is created for this Place and that CovidCase
        $this->createContextForCase($case, ['place_uuid' => $place->uuid]);

        $uniqueIndexCountRecentDays = Config::integer('misc.context.unique_index_count_recent_days');

        // When the date of symptom onset of the CovidCase is set to 60 days ago
        $case->date_of_symptom_onset = CarbonImmutable::now()->subDays($uniqueIndexCountRecentDays + 1);
        $case->save();

        // Then the index count at the Place is zero
        $place->refresh();
        self::assertEquals(0, $place->placeCounters->index_count);
    }

    public function testIndexCountSinceResetUpdatedWhenContextIsCreated(): void
    {
        // GIVEN a CovidCase
        $case = $this->createCase(['date_of_symptom_onset' => CarbonImmutable::now()]);

        // And a Place
        $place = $this->createPlace();

        // WHEN a Context is created for this Place and that CovidCase
        $this->createContextForCase($case, ['place_uuid' => $place->uuid]);

        // THEN the index count at the Place is one
        $place->refresh();
        self::assertEquals(1, $place->placeCounters->index_count_since_reset);
    }

    public function testIndexCountSinceResetUpdatedWhenContextIsRemovedFromPlace(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::now());

        // Given a CovidCase
        $case = $this->createCase(['episode_start_date' => CarbonImmutable::now()]);

        // And a Place
        $place = $this->createPlace();

        // And a Context associated with this Place and that CovidCase
        $context = $this->createContextForCase($case, ['place_uuid' => $place->uuid]);

        // And this context has a Moment some days ago
        $daysAgo = $this->faker->randomNumber(1);
        $day = CarbonImmutable::now()->subDays($daysAgo)->startOfDay();
        $this->createMomentForContext($context, ['day' => $day]);

        // And another Context associated with this Place and that CovidCase
        $anotherContext = $this->createContextForCase($case, ['place_uuid' => $place->uuid]);

        // And this context has a Moment one day before some days ago
        $oneMoreDayAgo = $daysAgo - 1;
        $anotherDay = CarbonImmutable::now()->subDays($oneMoreDayAgo)->startOfDay();
        $this->createMomentForContext($anotherContext, ['day' => $anotherDay]);

        // When the first Context is deleted
        $context->delete();

        // Then the index count at the Place is one
        $place->refresh();
        self::assertEquals(1, $place->placeCounters->index_count_since_reset);
    }

    public function testIndexCountSinceResetUpdatedWhenPlaceIsUpdated(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::now());

        // Given a CovidCase
        $case = $this->createCase(['episode_start_date' => CarbonImmutable::now()]);

        // And a Place
        $place = $this->createPlace();

        // When a Context is created for this Place and that CovidCase
        $this->createContextForCase($case, [
            'place_added_at' => CarbonImmutable::yesterday(),
        ]);

        $place->index_count_reset_at = CarbonImmutable::now();
        $place->save();

        // Then the index count at the Place is one
        $place->refresh();
        self::assertEquals(0, $place->placeCounters->index_count_since_reset);
    }
}
