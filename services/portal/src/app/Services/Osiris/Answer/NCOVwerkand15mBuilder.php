<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;

class NCOVwerkand15mBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        return Utils::mapYesNoUnknown($case->job->closeContactAtJob);
    }
}
