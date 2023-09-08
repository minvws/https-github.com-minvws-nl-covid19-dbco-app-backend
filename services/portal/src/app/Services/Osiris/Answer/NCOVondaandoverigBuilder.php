<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\UnderlyingSuffering\UnderlyingSufferingCommon;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;
use function implode;

class NCOVondaandoverigBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): string
    {
        // only when at an age < 70
        if (
            $case->index->dateOfBirth === null ||
            $case->createdAt->diff($case->index->dateOfBirth, true)->y >= 70
        ) {
            return '';
        }

        if (!isset($case->underlying_suffering->hasUnderlyingSuffering) || !isset($case->underlying_suffering->otherItems)) {
            return '';
        }

        assert($case->underlying_suffering instanceof UnderlyingSufferingCommon);
        assert($case->underlying_suffering->hasUnderlyingSuffering instanceof YesNoUnknown);

        // only when there was underlying suffering
        if (
            $case->underlying_suffering->hasUnderlyingSuffering !== YesNoUnknown::yes() ||
            $case->underlying_suffering->otherItems === null
        ) {
            return '';
        }

        return implode(',', $case->underlying_suffering->otherItems);
    }
}
