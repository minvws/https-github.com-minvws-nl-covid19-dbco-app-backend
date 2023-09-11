<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Eloquent\EloquentCase;
use Exception;

interface CaseFragmentsValidationService
{
    /**
     * @return array<string, array>
     *
     * @throws Exception
     */
    public function validateAllFragments(
        EloquentCase $case,
        array $filterTags = [],
        bool $stopOnFirstFailedSeverityLevel = true,
    ): array;
}
