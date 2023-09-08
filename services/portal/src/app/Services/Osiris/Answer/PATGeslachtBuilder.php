<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\Gender;

class PATGeslachtBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        return match ($case->index->gender) {
            Gender::male() => 'M',
            Gender::female() => 'V',
            default => 'Onb'
        };
    }
}
