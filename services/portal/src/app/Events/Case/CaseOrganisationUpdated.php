<?php

declare(strict_types=1);

namespace App\Events\Case;

use App\Models\Eloquent\EloquentCase;
use Illuminate\Foundation\Events\Dispatchable;

class CaseOrganisationUpdated
{
    use Dispatchable;

    public function __construct(
        public readonly EloquentCase $eloquentCase,
    ) {
    }
}
