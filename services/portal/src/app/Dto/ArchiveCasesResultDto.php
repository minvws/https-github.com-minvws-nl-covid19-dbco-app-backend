<?php

declare(strict_types=1);

namespace App\Dto;

use Illuminate\Support\Collection;

class ArchiveCasesResultDto
{
    /**
     * @param Collection<string> $closedCases List of Case UUIDs
     * @param Collection<array{uuid:string,caseId:string}> $invalidCases
     */
    public function __construct(
        public readonly Collection $closedCases,
        public readonly Collection $invalidCases,
    )
    {
    }
}
