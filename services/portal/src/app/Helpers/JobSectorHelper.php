<?php

declare(strict_types=1);

namespace App\Helpers;

use MinVWS\DBCO\Enum\Models\JobSector;
use MinVWS\DBCO\Enum\Models\JobSectorGroup;

use function array_map;
use function collect;

class JobSectorHelper
{
    /**
     * @param array $sectors<JobSector>
     */
    public static function containsCareGroup(array $sectors): bool
    {
        $groups = array_map(static function (JobSector $jobSector) {
            return $jobSector->group;
        }, $sectors);

        return collect($groups)->contains(JobSectorGroup::care());
    }
}
