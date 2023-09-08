<?php

declare(strict_types=1);

namespace App\Services\Policy\Calendar\StrategyLoader;

use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\DateOperationIdentifier;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

final class FixedStrategyLoader extends AbstractDateOperationStrategyLoader
{
    protected function getDateOperationsData(CalendarItemEnum $calendarItem, PolicyPersonType $personType): Collection
    {
        return Collection::make([
            $this->createDateOperationDto($personType, DateOperationIdentifier::default()),
        ]);
    }
}
