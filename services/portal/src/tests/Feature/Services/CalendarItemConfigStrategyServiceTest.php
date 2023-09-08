<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Models\Policy\DateOperation;
use App\Services\CalendarItemConfigStrategyService;
use App\Services\Policy\Calendar\StrategyLoader\StrategyLoader;
use App\Services\Policy\Calendar\StrategyLoader\StrategyLoaderFactory;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\DateOperationIdentifier;
use MinVWS\DBCO\Enum\Models\DateOperationMutation;
use MinVWS\DBCO\Enum\Models\IndexOriginDate;
use MinVWS\DBCO\Enum\Models\PeriodCalendarStrategyType;
use MinVWS\DBCO\Enum\Models\PointCalendarStrategyType;
use MinVWS\DBCO\Enum\Models\UnitOfTime;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('calendarItemConfigStrategy')]
final class CalendarItemConfigStrategyServiceTest extends FeatureTestCase
{
    public function testInitializeCalendarItemConfigStrategies(): void
    {
        Event::fake();

        $calendarItemConfig = CalendarItemConfig::factory()
            ->for(CalendarItem::factory()->point())
            ->create();
        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()
            ->recycle($calendarItemConfig)
            ->make([
                'strategy_type' => PointCalendarStrategyType::flexStrategy(),
            ]);

        /** @var Collection&MockInterface $initializeData */
        $initializeData = Mockery::mock(Collection::class);

        /** @var StrategyLoader&MockInterface $strategyLoader */
        $strategyLoader = Mockery::mock(StrategyLoader::class);
        $strategyLoader
            ->shouldReceive('getInitializeData')
            ->once()
            ->with($calendarItemConfigStrategy)
            ->andReturn($initializeData);
        $strategyLoader
            ->shouldReceive('set')
            ->once()
            ->with($calendarItemConfigStrategy, $initializeData)
            ->andReturn(Mockery::mock(Collection::class));

        /** @var StrategyLoaderFactory&MockInterface $strategyLoaderFactory */
        $strategyLoaderFactory = Mockery::mock(StrategyLoaderFactory::class);
        $strategyLoaderFactory
            ->shouldReceive('make')
            ->with(PointCalendarStrategyType::flexStrategy())
            ->once()
            ->andReturn($strategyLoader);

        /** @var ConnectionInterface&MockInterface $db */
        $db = Mockery::mock(ConnectionInterface::class);
        $db
            ->shouldReceive('transaction')
            ->once()
            ->with(Mockery::on(static function (callable $callback) {
                $callback();

                return true;
            }))
            ->andReturnNull();

        /** @var CalendarItemConfigStrategyService $service */
        $service = $this->app->make(CalendarItemConfigStrategyService::class, [
            'strategyLoaderFactory' => $strategyLoaderFactory,
            'db' => $db,
        ]);

        $service->initializeCalendarItemConfigStrategies($calendarItemConfigStrategy);
    }

    public function testUpdateCalendarItemConfigStrategies(): void
    {
        Event::fake();

        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()
            ->for(CalendarItemConfig::factory()->for(CalendarItem::factory()->index()->period()))
            ->create([
                'strategy_type' => PeriodCalendarStrategyType::fixedStrategy(),
            ]);
        $originalDefaultDateOperation = DateOperation::factory()
            ->recycle($calendarItemConfigStrategy)
            ->create([
                'identifier_type' => DateOperationIdentifier::default(),
            ]);

        $calendarItemConfigStrategy->strategy_type = PeriodCalendarStrategyType::flexStrategy();

        /** @var CalendarItemConfigStrategyService $service */
        $service = $this->app->make(CalendarItemConfigStrategyService::class);

        $service->updateCalendarItemConfigStrategies($calendarItemConfigStrategy);

        $this->assertDatabaseCount(DateOperation::class, 3);
        $this->assertDatabaseHas(DateOperation::class, [
            'calendar_item_config_strategy_uuid' => $calendarItemConfigStrategy->uuid,
            'identifier_type' => DateOperationIdentifier::default(),
            'mutation_type' => $originalDefaultDateOperation->mutation_type,
            'amount' => $originalDefaultDateOperation->amount,
            'unit_of_time_type' => $originalDefaultDateOperation->unit_of_time_type,
            'origin_date_type' => $originalDefaultDateOperation->origin_date_type,
        ]);
        $this->assertDatabaseHas(DateOperation::class, [
            'calendar_item_config_strategy_uuid' => $calendarItemConfigStrategy->uuid,
            'identifier_type' => DateOperationIdentifier::max(),
            'mutation_type' => DateOperationMutation::add(),
            'amount' => 0,
            'unit_of_time_type' => UnitOfTime::day(),
            'origin_date_type' => IndexOriginDate::defaultItem(),
        ]);
        $this->assertDatabaseHas(DateOperation::class, [
            'calendar_item_config_strategy_uuid' => $calendarItemConfigStrategy->uuid,
            'identifier_type' => DateOperationIdentifier::max(),
            'mutation_type' => DateOperationMutation::add(),
            'amount' => 0,
            'unit_of_time_type' => UnitOfTime::day(),
            'origin_date_type' => IndexOriginDate::defaultItem(),
        ]);
    }
}
