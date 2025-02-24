<?php

declare(strict_types=1);

namespace App\Events\Osiris;

use App\Dto\Osiris\Repository\CaseExportResult;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * @method static void dispatch(EloquentCase $case, CaseExportResult $caseExportResult, CaseExportType $caseExportType)
 */
final class CaseExportSucceeded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly EloquentCase $case,
        public readonly CaseExportResult $caseExportResult,
        public readonly CaseExportType $caseExportType,
    ) {
    }
}
