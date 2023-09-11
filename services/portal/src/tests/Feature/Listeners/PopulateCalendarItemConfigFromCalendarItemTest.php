<?php

declare(strict_types=1);

namespace Tests\Feature\Listeners;

use App\Events\CalendarItemCreated;
use App\Listeners\PopulateCalendarItemConfigFromCalendarItem;
use App\Models\Policy\CalendarItem;
use App\Services\CalendarItemConfigService;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
final class PopulateCalendarItemConfigFromCalendarItemTest extends FeatureTestCase
{
    public function testItListensToTheConfiguredEvents(): void
    {
        Event::fake();
        Event::assertListening(CalendarItemCreated::class, PopulateCalendarItemConfigFromCalendarItem::class);
    }

    public function testCreatingCalendarItemEmitsCalendarItemCreatedEvent(): void
    {
        Event::fake();

        CalendarItem::factory()->create();

        Event::assertDispatched(CalendarItemCreated::class);
    }

    public function testHandle(): void
    {
        $calendarItem = CalendarItem::factory()->make();

        /** @var CalendarItemConfigService&MockInterface $calendarItemConfigService */
        $calendarItemConfigService = Mockery::mock(CalendarItemConfigService::class);
        $calendarItemConfigService
            ->shouldReceive('createDefaultCalendarItemConfigsForNewCalendarItem')
            ->once()
            ->with($calendarItem)
            ->andReturnNull();

        /** @var PopulateCalendarItemConfigFromPolicyGuideline $listener */
        $listener = $this->app->make(PopulateCalendarItemConfigFromCalendarItem::class, [
            'calendarItemConfigService' => $calendarItemConfigService,
        ]);

        $listener->handle(new CalendarItemCreated($calendarItem));
    }
}
