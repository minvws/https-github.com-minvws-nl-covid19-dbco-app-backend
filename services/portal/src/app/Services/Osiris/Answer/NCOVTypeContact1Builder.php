<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use MinVWS\DBCO\Enum\Models\ContactCategory;

class NCOVTypeContact1Builder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        $sources = Utils::getSources($case);
        if ($sources->count() !== 1) {
            return null;
        }

        /** @var EloquentTask $source */
        $source = $sources[0];
        return match ($source->category) {
            ContactCategory::cat1() => '1',
            ContactCategory::cat2a() => '2',
            ContactCategory::cat2b() => '2',
            ContactCategory::cat3a() => '3',
            ContactCategory::cat3b() => '3',
            default => '4'
        };
    }
}
