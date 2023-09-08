<?php

declare(strict_types=1);

namespace Tests\Feature\Listeners;

use App\Events\CalendarItemConfigStrategyUpdated;
use App\Listeners\UpdateCalendarItemConfigStrategies;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Services\CalendarItemConfigStrategyService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\PeriodCalendarStrategyType;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
final class UpdateCalendarItemConfigStrategiesTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    public function testItCanBeInitialized(): void
    {
        $updateCalendarItemConfigStrategies = $this->app->make(UpdateCalendarItemConfigStrategies::class);

        $this->assertInstanceOf(UpdateCalendarItemConfigStrategies::class, $updateCalendarItemConfigStrategies);
    }

    public function testItListensToTheConfiguredEvents(): void
    {
        Event::assertListening(CalendarItemConfigStrategyUpdated::class, UpdateCalendarItemConfigStrategies::class);
    }

    public function testUpdatingCalendarItemEmitsCalendarItemUpdatedEvent(): void
    {
        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()->create();

        Event::assertNotDispatched(CalendarItemConfigStrategyUpdated::class);

        $calendarItemConfigStrategy->created_at = CarbonImmutable::now()->subDay();
        $calendarItemConfigStrategy->save();

        Event::assertDispatched(CalendarItemConfigStrategyUpdated::class);
    }

    public function testItDoesNothingIfStrategyLoadersAreNotUpdated(): void
    {
        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()->make();
        $calendarItemConfigStrategy->syncOriginal();

        $calendarItemConfigStrategy->created_at = CarbonImmutable::now()->subDay();

        $event = new CalendarItemConfigStrategyUpdated($calendarItemConfigStrategy);

        /** @var CalendarItemConfigStrategyService&MockInterface $service */
        $service = Mockery::mock(CalendarItemConfigStrategyService::class);
        $service->shouldNotReceive('updateCalendarItemConfigStrategies')->andReturnNull();

        /** @var UpdateCalendarItemConfigStrategies $updateCalendarItemConfigStrategies */
        $updateCalendarItemConfigStrategies = $this->app->make(
            UpdateCalendarItemConfigStrategies::class,
            [
                'calendarItemConfigStrategyService' => $service,
            ],
        );

        $updateCalendarItemConfigStrategies->handle($event);
    }

    public function testHandle(): void
    {
        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()->make([
            'strategy_type' => PeriodCalendarStrategyType::fixedStrategy(),
        ]);
        $calendarItemConfigStrategy->syncOriginal();

        $calendarItemConfigStrategy->strategy_type = PeriodCalendarStrategyType::flexStrategy();

        $event = new CalendarItemConfigStrategyUpdated($calendarItemConfigStrategy);

        /** @var CalendarItemConfigStrategyService&MockInterface $service */
        $service = Mockery::mock(CalendarItemConfigStrategyService::class);
        $service
            ->shouldReceive('updateCalendarItemConfigStrategies')
            ->once()
            ->with($calendarItemConfigStrategy)
            ->andReturnNull();

        /** @var UpdateCalendarItemConfigStrategies $updateCalendarItemConfigStrategies */
        $updateCalendarItemConfigStrategies = $this->app->make(
            UpdateCalendarItemConfigStrategies::class,
            [
                'calendarItemConfigStrategyService' => $service,
            ],
        );

        $updateCalendarItemConfigStrategies->handle($event);
    }
}
