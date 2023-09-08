<?php

declare(strict_types=1);

namespace App\Dto\OsirisHistory;

use MinVWS\DBCO\Enum\Models\OsirisHistoryStatus;

class OsirisHistoryDto
{
    public function __construct(
        public readonly string $caseUuid,
        public readonly OsirisHistoryStatus $status,
        public readonly string $osirisStatus,
        public readonly ?OsirisHistoryValidationResponse $osirisValidationResponse,
    )
    {
    }
}
