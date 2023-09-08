<?php

declare(strict_types=1);

namespace App\Dto\Admin;

use MinVWS\DBCO\Enum\Models\ContactOriginDate;
use MinVWS\DBCO\Enum\Models\DateOperationIdentifier;
use MinVWS\DBCO\Enum\Models\DateOperationMutation;
use MinVWS\DBCO\Enum\Models\IndexOriginDate;
use MinVWS\DBCO\Enum\Models\UnitOfTime;

final class CreateDateOperationDto
{
    public function __construct(
        public readonly DateOperationIdentifier $identifier,
        public readonly DateOperationMutation $mutation,
        public readonly int $amount,
        public readonly UnitOfTime $unitOfTime,
        public readonly IndexOriginDate|ContactOriginDate $originDate,
    )
    {
    }

    public function toEloquentAttributes(): array
    {
        return [
            'identifier_type' => $this->identifier,
            'mutation_type' => $this->mutation,
            'amount' => $this->amount,
            'unit_of_time_type' => $this->unitOfTime,
            'origin_date_type' => $this->originDate,
        ];
    }
}
