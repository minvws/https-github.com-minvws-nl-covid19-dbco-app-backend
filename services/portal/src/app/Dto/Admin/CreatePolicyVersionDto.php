<?php

declare(strict_types=1);

namespace App\Dto\Admin;

use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;

class CreatePolicyVersionDto
{
    public readonly PolicyVersionStatus $status;

    public function __construct(
        public readonly string $name,
        public readonly CarbonImmutable $startDate,
        ?PolicyVersionStatus $status = null,
    )
    {
        $this->status = $status ?? PolicyVersionStatus::draft();
    }

    public function toEloquentAttributes(): array
    {
        return [
            'name' => $this->name,
            'start_date' => $this->startDate,
            'status' => $this->status,
        ];
    }
}
