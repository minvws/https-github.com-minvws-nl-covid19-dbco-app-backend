<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Policy\Calendar;

use App\Dto\Admin\UpdateCalendarItemConfigStrategyDto;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Repositories\Policy\CalendarItemConfigStrategyRepository;
use App\Services\Policy\Calendar\CalendarItemConfigStrategyService;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('calendarItemConfig')]
final class CalendarItemConfigStrategyServiceTest extends FeatureTestCase
{
    private CalendarItemConfigStrategyRepository&MockInterface $calendarItemConfigStrategyRepository;
    private CalendarItemConfigStrategyService $calendarItemConfigStrategyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calendarItemConfigStrategyRepository = Mockery::mock(CalendarItemConfigStrategyRepository::class);
        $this->app->instance(CalendarItemConfigStrategyRepository::class, $this->calendarItemConfigStrategyRepository);

        $this->calendarItemConfigStrategyService = $this->app->make(CalendarItemConfigStrategyService::class);
    }

    public function testUpdateCalendarItemConfig(): void
    {
        /** @var MockInterface&UpdateCalendarItemConfigStrategyDto $dto */
        $dto = Mockery::mock(UpdateCalendarItemConfigStrategyDto::class);
        $calendarItemConfigStrategy = new CalendarItemConfigStrategy();
        $mockedResult = new CalendarItemConfigStrategy();

        $this->calendarItemConfigStrategyRepository
            ->shouldReceive('updateCalendarItemConfigStrategy')
            ->once()
            ->with($calendarItemConfigStrategy, $dto)
            ->andReturn($mockedResult);

        $result = $this->calendarItemConfigStrategyService->updateCalendarItemConfigStrategy($calendarItemConfigStrategy, $dto);

        $this->assertSame($result, $mockedResult);
    }
}
