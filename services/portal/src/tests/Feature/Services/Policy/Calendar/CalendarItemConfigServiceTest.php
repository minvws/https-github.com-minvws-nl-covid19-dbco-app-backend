<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Policy\Calendar;

use App\Dto\Admin\UpdateCalendarItemConfigDto;
use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\PolicyGuideline;
use App\Repositories\Policy\CalendarItemConfigRepository;
use App\Services\Policy\Calendar\CalendarItemConfigService;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('calendarItemConfig')]
final class CalendarItemConfigServiceTest extends FeatureTestCase
{
    private CalendarItemConfigRepository&MockInterface $calendarItemConfigRepository;
    private CalendarItemConfigService $calendarItemConfigService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calendarItemConfigRepository = Mockery::mock(CalendarItemConfigRepository::class);
        $this->app->instance(CalendarItemConfigRepository::class, $this->calendarItemConfigRepository);

        $this->calendarItemConfigService = $this->app->make(CalendarItemConfigService::class);
    }

    public function testGetCalendarItemConfigs(): void
    {
        $policyGuideline = new PolicyGuideline();
        $mockedResult = Collection::make();

        $this->calendarItemConfigRepository
            ->shouldReceive('getCalendarItemConfigsByPolicyGuideline')
            ->once()
            ->with($policyGuideline)
            ->andReturn($mockedResult);

        $result = $this->calendarItemConfigService->getCalendarItemConfigs($policyGuideline);

        $this->assertSame($result, $mockedResult);
    }

    public function testUpdateCalendarItemConfig(): void
    {
        /** @var MockInterface&UpdateCalendarItemConfigDto $dto */
        $dto = Mockery::mock(UpdateCalendarItemConfigDto::class);
        $calendarItemConfig = new CalendarItemConfig();
        $mockedResult = new CalendarItemConfig();

        $this->calendarItemConfigRepository
            ->shouldReceive('updateCalendarItemConfig')
            ->once()
            ->with($calendarItemConfig, $dto)
            ->andReturn($mockedResult);

        $result = $this->calendarItemConfigService->updateCalendarItemConfig($calendarItemConfig, $dto);

        $this->assertSame($result, $mockedResult);
    }
}
