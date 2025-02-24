<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;

class MELGGDOntvDtBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        return Utils::formatDate($case->createdAt);
    }
}
