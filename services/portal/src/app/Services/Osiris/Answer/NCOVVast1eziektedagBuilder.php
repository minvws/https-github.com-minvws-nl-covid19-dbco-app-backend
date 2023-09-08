<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class NCOVVast1eziektedagBuilder extends AbstractSingleValueBuilder
{
    public const UNKNOWN = 'onb';
    public const NOT_APPLICABLE = 'NVT';
    public const ESTIMATED = 'G';
    public const DETERMINED = 'V';

    protected function getValue(EloquentCase $case): ?string
    {
        assert(isset($case->test));
        assert($case->test instanceof Test);

        if ($case->symptoms->hasSymptoms === YesNoUnknown::no()) {
            return self::NOT_APPLICABLE;
        }

        if ($case->test->dateOfSymptomOnset !== null) {
            return match ($case->test->isSymptomOnsetEstimated) {
                true => self::ESTIMATED,
                false => self::DETERMINED,
                default => self::UNKNOWN
            };
        }

        //Date of symptom onset is null, and hasSymptoms is either Yes or Unknown
        return self::UNKNOWN;
    }
}
