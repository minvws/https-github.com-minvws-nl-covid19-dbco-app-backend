<?php

declare(strict_types=1);

namespace Tests\Feature\Listeners;

use App\Events\CalendarItemConfigStrategyCreated;
use App\Listeners\InitializeCalendarItemConfigStrategies;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Services\CalendarItemConfigStrategyService;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
final class InitializeCalendarItemConfigStrategiesTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    public function testItListensToTheConfiguredEvents(): void
    {
        Event::assertListening(CalendarItemConfigStrategyCreated::class, InitializeCalendarItemConfigStrategies::class);
    }

    public function testCreatingCalendarItemEmitsCalendarItemCreatedEvent(): void
    {
        CalendarItemConfigStrategy::factory()->create();

        Event::assertDispatched(CalendarItemConfigStrategyCreated::class);
    }

    public function testHandle(): void
    {
        $calendarItemConfigStrategy = CalendarItemConfigStrategy::factory()->make();

        /** @var CalendarItemConfigStrategyService&MockInterface $calendarItemConfigStrategyService */
        $calendarItemConfigStrategyService = Mockery::mock(CalendarItemConfigStrategyService::class);
        $calendarItemConfigStrategyService
            ->shouldReceive('initializeCalendarItemConfigStrategies')
            ->once()
            ->with($calendarItemConfigStrategy)
            ->andReturnNull();

        /** @var PopulateCalendarItemConfigFromPolicyGuideline $listener */
        $listener = $this->app->make(InitializeCalendarItemConfigStrategies::class, [
            'calendarItemConfigStrategyService' => $calendarItemConfigStrategyService,
        ]);

        $listener->handle(new CalendarItemConfigStrategyCreated($calendarItemConfigStrategy));
    }
}
