<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Models\CovidCase;

use App\Dto\TestResultReport\Triage;
use App\Models\CovidCase\Symptoms;
use App\Models\Versions\CovidCase\Symptoms\SymptomsV2;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

final class SymptomsFactory
{
    public static function create(Triage $triage): SymptomsV2
    {
        /** @var SymptomsV2 $symptoms */
        $symptoms = Symptoms::getSchema()->getVersion(2)->newInstance();

        if ($triage->dateOfFirstSymptom instanceof DateTimeInterface) {
            $symptoms->hasSymptoms = YesNoUnknown::yes();
        }

        return $symptoms;
    }
}
