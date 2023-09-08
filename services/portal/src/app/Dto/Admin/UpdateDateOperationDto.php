<?php

declare(strict_types=1);

namespace App\Dto\Admin;

use MinVWS\DBCO\Enum\Models\ContactOriginDate;
use MinVWS\DBCO\Enum\Models\DateOperationRelativeDay;
use MinVWS\DBCO\Enum\Models\IndexOriginDate;

final class UpdateDateOperationDto
{
    public function __construct(
        public readonly DateOperationRelativeDay $relativeDay,
        public readonly IndexOriginDate|ContactOriginDate $originDateType,
    ) {
    }

    public function toEloquentAttributes(): array
    {
        return [
            'relative_day' => $this->relativeDay,
            'origin_date_type' => $this->originDateType,
        ];
    }
}
