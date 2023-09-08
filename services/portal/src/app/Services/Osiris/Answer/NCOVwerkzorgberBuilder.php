<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\JobSectorGroup;
use MinVWS\DBCO\Enum\Models\ProfessionCare;

use function array_intersect;
use function count;

class NCOVwerkzorgberBuilder extends AbstractSingleValueBuilder
{
    protected function getValue(EloquentCase $case): ?string
    {
        $professionCare = $case->job->professionCare;
        $sectors = $case->job->sectors;

        if ($professionCare === null || $sectors === null) {
            return null;
        }

        $careSectors = JobSectorGroup::care()->categories;
        if (count(array_intersect($sectors, $careSectors)) === 0) {
            return null;
        }

        return match ($professionCare) {
            ProfessionCare::verzorger() => '1',
            ProfessionCare::verpleegkundige() => '2',
            ProfessionCare::arts() => '3',
            ProfessionCare::tandarts() => '4',
            ProfessionCare::dietist() => '5',
            ProfessionCare::huidtherapeut() => '6',
            ProfessionCare::logopedist() => '7',
            ProfessionCare::fysiotherapeut() => '8',
            ProfessionCare::orthoptist() => '9',
            ProfessionCare::audiocien() => '10',
            ProfessionCare::thuiszorg() => '11',
            ProfessionCare::anders() => '12',
            default => null
        };
    }
}
