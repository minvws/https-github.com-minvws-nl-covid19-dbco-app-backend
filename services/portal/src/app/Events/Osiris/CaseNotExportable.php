<?php

declare(strict_types=1);

namespace App\Events\Osiris;

use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * @method static void dispatch(EloquentCase $case, CaseExportType $caseExportType)
 */
class CaseNotExportable
{
    use Dispatchable;

    public function __construct(
        public readonly EloquentCase $case,
        public readonly CaseExportType $caseExportType,
    ) {
    }
}
