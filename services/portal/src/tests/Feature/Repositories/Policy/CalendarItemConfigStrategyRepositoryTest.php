<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Policy;

use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Models\Policy\PolicyVersion;
use App\Repositories\Policy\CalendarItemConfigStrategyRepository;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\CalendarItemConfigStrategyIdentifierType;
use MinVWS\DBCO\Enum\Models\PeriodCalendarStrategyType;
use MinVWS\DBCO\Enum\Models\PointCalendarStrategyType;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('calendarItemConfigStrategy')]
final class CalendarItemConfigStrategyRepositoryTest extends FeatureTestCase
{
    public function testCreateDefaultCalendarItemConfigStrategiesForNewCalendarItemConfigs(): void
    {
        Event::fake();

        /** @var CalendarItemConfigStrategyRepository $calendarItemConfigStrategyRepository */
        $calendarItemConfigStrategyRepository = $this->app->make(CalendarItemConfigStrategyRepository::class);

        $policyVersion = PolicyVersion::factory()->create();

        $periodCalendarItemConfigs = CalendarItemConfig::factory()
            ->recycle($policyVersion)
            ->for(CalendarItem::factory()->period())
            ->count(2)
            ->create();

        $pointCalendarItemConfig = CalendarItemConfig::factory()->for(CalendarItem::factory()->point())->createOne();

        $this->assertDatabaseCount(CalendarItemConfigStrategy::class, 0);

        $result = $calendarItemConfigStrategyRepository
            ->createDefaultCalendarItemConfigStrategiesForNewCalendarItemConfigs(
                (clone $periodCalendarItemConfigs)->add($pointCalendarItemConfig),
            );

        $this->assertCount(5, $result);
        $this->assertDatabaseCount(CalendarItemConfigStrategy::class, 5);

        $periodCalendarItemConfigs
            ->each(function (CalendarItemConfig $calendarItemConfig): void {
                $this->assertDatabaseHas(CalendarItemConfigStrategy::class, [
                    'calendar_item_config_uuid' => $calendarItemConfig->uuid,
                    'identifier_type' => CalendarItemConfigStrategyIdentifierType::periodStart(),
                    'strategy_type' => PeriodCalendarStrategyType::fixedStrategy(),
                ]);
                $this->assertDatabaseHas(CalendarItemConfigStrategy::class, [
                    'calendar_item_config_uuid' => $calendarItemConfig->uuid,
                    'identifier_type' => CalendarItemConfigStrategyIdentifierType::periodEnd(),
                    'strategy_type' => PeriodCalendarStrategyType::fixedStrategy(),
                ]);
            });

        $this->assertDatabaseHas(CalendarItemConfigStrategy::class, [
            'calendar_item_config_uuid' => $pointCalendarItemConfig->uuid,
            'identifier_type' => CalendarItemConfigStrategyIdentifierType::point(),
            'strategy_type' => PointCalendarStrategyType::fixedStrategy(),
        ]);
    }

    public function testLoadMissing(): void
    {
        /** @var CalendarItemConfigStrategyRepository $calendarItemConfigStrategyRepository */
        $calendarItemConfigStrategyRepository = $this->app->make(CalendarItemConfigStrategyRepository::class);

        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()->make();
        $relations = ['foobar'];

        /** @var CalendarItemConfigStrategy&MockInterface $CalendarItemConfigStrategyMock */
        $CalendarItemConfigStrategyMock = Mockery::mock(CalendarItemConfigStrategy::class);
        $CalendarItemConfigStrategyMock
            ->shouldReceive('loadMissing')
            ->with(...$relations)
            ->once()
            ->andReturn($calendarItemConfigStrategy);

        $calendarItemConfigStrategyRepository->loadMissing($CalendarItemConfigStrategyMock, ...$relations);
    }
}
