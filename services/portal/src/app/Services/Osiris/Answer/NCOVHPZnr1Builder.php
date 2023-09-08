<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;

class NCOVHPZnr1Builder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        $sources = Utils::getSources($case);
        if ($sources->count() !== 1) {
            return null;
        }

        /** @var EloquentTask $source */
        $source = $sources[0];
        return $source->dossier_number;
    }
}
