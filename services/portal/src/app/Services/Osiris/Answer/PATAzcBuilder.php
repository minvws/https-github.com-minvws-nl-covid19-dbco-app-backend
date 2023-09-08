<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV1UpTo4;
use App\Models\Versions\CovidCase\CovidCaseV5Up;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;
use function collect;

class PATAzcBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        if ($case instanceof CovidCaseV5Up) {
            return null;
        }

        assert($case instanceof CovidCaseV1UpTo4);
        if ($case->sourceEnvironments->hasLikelySourceEnvironments !== YesNoUnknown::yes()) {
            return null;
        }

        $sourceEnvironments = collect($case->sourceEnvironments->likelySourceEnvironments);
        if ($sourceEnvironments->isEmpty()) {
            return null;
        }

        return $sourceEnvironments->contains(ContextCategory::asielzoekerscentrum()) ? 'J' : 'N';
    }
}
