<?php

declare(strict_types=1);

namespace App\Events\Osiris;

use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * @method static void dispatch(EloquentCase $case, CaseExportType $caseExportType, array $errors = null)
 */
final class CaseExportRejected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly EloquentCase $case,
        public readonly CaseExportType $caseExportType,
        /** @var array<int,string> $errors */
        public readonly array $errors = [],
    ) {
    }
}
