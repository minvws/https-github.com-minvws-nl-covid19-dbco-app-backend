<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Policy;

use App\Events\PolicyVersionCreated;
use App\Models\Policy\CalendarView;
use App\Models\Policy\PolicyVersion;
use App\Repositories\Policy\CalendarItemPopulator;
use App\Repositories\Policy\CalendarViewPopulator;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('calendarView')]
final class CalendarViewPopulatorTest extends FeatureTestCase
{
    private CalendarItemPopulator $calendarItemPopulator;
    private CalendarViewPopulator $calendarViewPopulator;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PolicyVersionCreated::class]);

        $this->calendarItemPopulator = $this->app->make(CalendarItemPopulator::class);
        $this->calendarViewPopulator = $this->app->make(CalendarViewPopulator::class);
    }

    public function testPopulate(): void
    {
        $policyVersion = PolicyVersion::factory()->create();

        $this->calendarItemPopulator->populate($policyVersion);

        $this->calendarViewPopulator->populate($policyVersion);

        $this->assertDatabaseCount(CalendarView::class, 7);
        $this->assertDatabaseCount('calendar_view_calendar_item', 11);
    }
}
