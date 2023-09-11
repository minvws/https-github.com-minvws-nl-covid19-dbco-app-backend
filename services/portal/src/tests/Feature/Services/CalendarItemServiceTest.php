<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Dto\Admin\CreateCalendarItemDto;
use App\Dto\Admin\UpdateCalendarItemDto;
use App\Models\Policy\CalendarItem;
use App\Repositories\Policy\CalendarItemRepository;
use App\Services\CalendarItemService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use MinVWS\DBCO\Enum\Models\FixedCalendarItem;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('calendarItem')]
final class CalendarItemServiceTest extends FeatureTestCase
{
    private CalendarItemRepository&MockInterface $calendarItemRepository;
    private CalendarItemService $calendarItemService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calendarItemRepository = Mockery::mock(CalendarItemRepository::class);
        $this->app->instance(CalendarItemRepository::class, $this->calendarItemRepository);

        $this->calendarItemService = $this->app->make(CalendarItemService::class);
    }

    public function testGetCalendarItems(): void
    {
        $uuid = $this->faker->uuid();
        $policyPersonType = PolicyPersonType::index();
        $mockedResult = Collection::make();

        $this->calendarItemRepository
            ->shouldReceive('getCalendarItems')
            ->once()
            ->with($uuid, $policyPersonType)
            ->andReturn($mockedResult);

        $result = $this->calendarItemService->getCalendarItems($uuid, $policyPersonType);

        $this->assertSame($result, $mockedResult);
    }

    public function testDeleteCalendarItem(): void
    {
        $calendarItem = new CalendarItem();
        $mockedResult = $this->faker->boolean();

        $this->calendarItemRepository
            ->shouldReceive('deleteCalendarItem')
            ->once()
            ->with($calendarItem)
            ->andReturn($mockedResult);

        $result = $this->calendarItemService->deleteCalendarItem($calendarItem);

        $this->assertSame($result, $mockedResult);
    }

    public function testDeleteCalendarItemOnNonDeletableItem(): void
    {
        $calendarItem = new CalendarItem(['fixed_calendar_item_enum' => FixedCalendarItem::source()]);

        $this->expectExceptionObject(ValidationException::withMessages(['general' => 'This calendar item cannot be deleted!']));

        $this->calendarItemService->deleteCalendarItem($calendarItem);
    }

    public function testCreateCalendarItem(): void
    {
        $policyVersionUuid = $this->faker->uuid();
        /** @var MockInterface&CreateCalendarItemDto $dto */
        $dto = Mockery::mock(CreateCalendarItemDto::class);
        $mockedResult = new CalendarItem();

        $this->calendarItemRepository
            ->shouldReceive('createCalendarItem')
            ->once()
            ->with($policyVersionUuid, $dto)
            ->andReturn($mockedResult);

        $result = $this->calendarItemService->createCalendarItem($policyVersionUuid, $dto);

        $this->assertSame($result, $mockedResult);
    }

    public function testUpdateCalendarItem(): void
    {
        /** @var MockInterface&UpdateCalendarItemDto $dto */
        $dto = Mockery::mock(UpdateCalendarItemDto::class);
        $calendarItem = new CalendarItem();
        $mockedResult = new CalendarItem();

        $this->calendarItemRepository
            ->shouldReceive('updateCalendarItem')
            ->once()
            ->with($calendarItem, $dto)
            ->andReturn($mockedResult);

        $result = $this->calendarItemService->updateCalendarItem($calendarItem, $dto);

        $this->assertSame($result, $mockedResult);
    }
}
