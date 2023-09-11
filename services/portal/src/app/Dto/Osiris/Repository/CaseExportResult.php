<?php

declare(strict_types=1);

namespace App\Dto\Osiris\Repository;

use App\ValueObjects\OsirisNumber;

final class CaseExportResult
{
    public function __construct(
        public readonly OsirisNumber $osirisNumber,
        public readonly int $questionnaireVersion,
        public readonly string $reportNumber,
        public readonly string $caseUuid,
        public readonly array $warnings = [],
    ) {
    }
}
