<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Traits;

use MinVWS\DBCO\Enum\Models\JobSector;

trait JobSectorGroupJobSectors
{
    /**
     * Returns the job sectors for this group.
     *
     * @return JobSector[]
     */
    protected function getJobSectors(): array
    {
        return array_filter(JobSector::all(), fn (JobSector $c) => $c->group === $this);
    }
}
