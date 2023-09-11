<?php

declare(strict_types=1);

namespace App\Services\Policy\Calendar;

use App\Dto\Admin\UpdateCalendarItemConfigDto;
use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\PolicyGuideline;
use App\Repositories\Policy\CalendarItemConfigRepository;
use Illuminate\Support\Collection;

final class CalendarItemConfigService
{
    public function __construct(private CalendarItemConfigRepository $calendarItemConfigRepository)
    {
    }

    /**
     * @return Collection<CalendarItemConfig>
     */
    public function getCalendarItemConfigs(PolicyGuideline $policyGuideline): Collection
    {
        return $this->calendarItemConfigRepository->getCalendarItemConfigsByPolicyGuideline($policyGuideline);
    }

    public function updateCalendarItemConfig(CalendarItemConfig $calendarItemConfig, UpdateCalendarItemConfigDto $dto): CalendarItemConfig
    {
        return $this->calendarItemConfigRepository->updateCalendarItemConfig($calendarItemConfig, $dto);
    }
}
