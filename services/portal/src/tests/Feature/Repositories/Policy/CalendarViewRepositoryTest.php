<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\Policy;

use App\Dto\Admin\UpdateCalendarViewDto;
use App\Events\PolicyVersionCreated;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarView;
use App\Models\Policy\PolicyVersion;
use App\Repositories\Policy\CalendarViewRepository;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\CalendarView as CalendarViewEnum;
use PhpOption\None;
use PhpOption\Some;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('calendarView')]
final class CalendarViewRepositoryTest extends FeatureTestCase
{
    private CalendarViewRepository $calendarViewRepository;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PolicyVersionCreated::class]);

        $this->calendarViewRepository = $this->app->make(CalendarViewRepository::class);
    }

    public function testGetCalendarView(): void
    {
        $policyVersionOne = PolicyVersion::factory()->create();

        $calendarView = CalendarView::factory()
            ->recycle($policyVersionOne)
            ->create();

        $actualCalendarView = $this->calendarViewRepository->getCalendarView($calendarView->uuid);

        $this->assertEquals($calendarView->uuid, $actualCalendarView->uuid);
    }

    public function testGetCalendarViews(): void
    {
        $policyVersionOne = PolicyVersion::factory()->create();
        $policyVersionTwo = PolicyVersion::factory()->create();

        CalendarView::factory()
            ->recycle($policyVersionOne)
            ->count(3)
            ->create();

        CalendarView::factory()
            ->recycle($policyVersionTwo)
            ->count(2)
            ->create();

        $actualCalendarItems = $this->calendarViewRepository->getCalendarViews($policyVersionOne->uuid);

        $this->assertCount(3, $actualCalendarItems);
    }

    public function testUpdateCalendarView(): void
    {
        $policyVersion = PolicyVersion::factory()->create();
        $calendarView = CalendarView::factory()
            ->recycle($policyVersion)
            ->create([
                'calendar_view_enum' => CalendarViewEnum::indexSidebar(),
            ]);

        $calendarItems = CalendarItem::factory()->count(4)->create();
        $dto = new UpdateCalendarViewDto(None::create(), Some::create($calendarItems->pluck('uuid')->toArray()));

        $updatedCalendarView = $this->calendarViewRepository->updateCalendarView($calendarView, $dto);

        $this->assertSame($calendarView, $updatedCalendarView);
        $this->assertEquals(4, $updatedCalendarView->calendarItems()->count());
    }

    public function testUpdateCalendarViewRemoveAllCalendarItems(): void
    {
        $policyVersion = PolicyVersion::factory()->create();
        $calendarView = CalendarView::factory()
            ->recycle($policyVersion)
            ->withCalendarItems($policyVersion)
            ->create([
                'calendar_view_enum' => CalendarViewEnum::indexSidebar(),
            ]);

        $dto = new UpdateCalendarViewDto(None::create(), Some::create([]));

        $updatedCalendarView = $this->calendarViewRepository->updateCalendarView($calendarView, $dto);

        $this->assertSame($calendarView, $updatedCalendarView);
        $this->assertEquals(0, $updatedCalendarView->calendarItems()->count());
    }
}
