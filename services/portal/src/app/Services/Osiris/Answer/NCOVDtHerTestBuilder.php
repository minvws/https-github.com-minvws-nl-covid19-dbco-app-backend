<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\SelfTestIndicator;

use function in_array;

class NCOVDtHerTestBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        if ($case->test->infectionIndicator === InfectionIndicator::labTest()) {
            return null;
        }

        if (!in_array($case->test->selfTestIndicator, [SelfTestIndicator::molecular(), SelfTestIndicator::antigen()], true)) {
            return null;
        }

        return Utils::formatDate($case->test->selfTestLabTestDate);
    }
}
