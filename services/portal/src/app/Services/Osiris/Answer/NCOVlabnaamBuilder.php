<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\TestResultSource;

use function assert;

class NCOVlabnaamBuilder extends AbstractSingleValueBuilder
{
    /*
     * Laboratorium dat de PCR test heeft uitgevoerd
     */
    protected function getValue(EloquentCase $case): ?string
    {
        assert(isset($case->test));
        assert($case->test instanceof Test);

        if ($case->test->source === TestResultSource::coronit()->value) {
            return null;
        }

        return $case->test->source;
    }
}
