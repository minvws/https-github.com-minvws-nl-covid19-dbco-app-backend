<?php

declare(strict_types=1);

namespace App\Dto\Admin;

use MinVWS\DBCO\Enum\Models\PeriodCalendarStrategyType;
use MinVWS\DBCO\Enum\Models\PointCalendarStrategyType;

final class UpdateCalendarItemConfigStrategyDto
{
    public function __construct(
        public readonly PointCalendarStrategyType|PeriodCalendarStrategyType $strategyType,
    ) {
    }

    public function toEloquentAttributes(): array
    {
        return [
            'strategy_type' => $this->strategyType,
        ];
    }
}
