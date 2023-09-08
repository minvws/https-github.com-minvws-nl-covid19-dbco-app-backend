<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV1UpTo4;
use App\Models\Versions\CovidCase\CovidCaseV5Up;
use MinVWS\DBCO\Enum\Models\EduDaycareType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class NCOVStudentLLBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        if ($case instanceof CovidCaseV5Up) {
            return null;
        }
        assert($case instanceof CovidCaseV1UpTo4);

        if ($case->eduDaycare->isStudent !== YesNoUnknown::yes()) {
            return null;
        }

        return match ($case->eduDaycare->type) {
            EduDaycareType::vocationalOrProfessionalEducationOrUniversity() => '1',
            EduDaycareType::secondaryEducation() => '2',
            EduDaycareType::primaryEducation() => '3',
            default => '5' // unknown
        };
    }
}
