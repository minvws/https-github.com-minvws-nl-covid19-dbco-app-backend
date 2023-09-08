<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;

use function assert;

class ZIE1eZiekteDtBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        assert(isset($case->test));
        assert($case->test instanceof Test);

        if ($case->test->isSymptomOnsetEstimated === null) {
            return null;
        }

        return Utils::formatDate($case->test->dateOfSymptomOnset);
    }
}
