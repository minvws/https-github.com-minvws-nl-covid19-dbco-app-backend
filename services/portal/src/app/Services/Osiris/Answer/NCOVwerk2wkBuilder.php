<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use MinVWS\DBCO\Enum\Models\JobSector;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function array_diff;
use function array_map;
use function array_unique;
use function count;
use function in_array;

class NCOVwerk2wkBuilder extends AbstractMultiValueBuilder
{
    private const NOT_APPLICABLE = '16';
    private const UNKNOWN = '17';

    protected function getValues(EloquentCase $case): array
    {
        if ($case->job->wasAtJob === null || $case->job->wasAtJob === YesNoUnknown::unknown()) {
            return [self::UNKNOWN];
        }

        if ($case->job->wasAtJob === YesNoUnknown::no()) {
            return [self::NOT_APPLICABLE];
        }

        $sectors = $case->job->sectors;
        if ($sectors === null || count($sectors) === 0) {
            return [self::UNKNOWN];
        }

        $values = array_map(fn (JobSector $sector) => $this->mapJobSector($sector), $sectors);
        $values = array_unique($values);

        if (count($values) > 1 && in_array(self::UNKNOWN, $values, true)) {
            $values = array_diff($values, [self::UNKNOWN]);
        }

        return $values;
    }

    private function mapJobSector(JobSector $sector): string
    {
        return match ($sector) {
            JobSector::ziekenhuis() => '1',
            JobSector::verpleeghuisOfVerzorgingshuis() => '2',
            JobSector::andereZorg() => '3',
            JobSector::dagopvang() => '5',
            JobSector::basisschoolEnBuitenschoolseOpvang() => '6',
            JobSector::middelbaarOnderwijsOfMiddelbaarBeroepsonderwijs() => '7',
            JobSector::medewerkerHogerOnderwijs() => '8',
            JobSector::werkMetDierenOfDierlijkeProducten() => '13',
            JobSector::werkMetEtenOfDrinken() => '14',
            JobSector::horeca() => '9',
            JobSector::mantelzorg() => '12',
            JobSector::openbaarvervoer() => '10',
            JobSector::politieBrandweer() => '11',
            JobSector::andereBeroep() => '15',
            default => self::UNKNOWN
        };
    }
}
