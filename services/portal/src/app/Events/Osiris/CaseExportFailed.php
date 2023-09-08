<?php

declare(strict_types=1);

namespace App\Events\Osiris;

use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * @method static void dispatch(EloquentCase $case, CaseExportType $caseExportType)
 */
final class CaseExportFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly EloquentCase $case,
        public readonly CaseExportType $caseExportType,
    ) {
    }
}
