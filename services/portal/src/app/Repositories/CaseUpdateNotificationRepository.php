<?php

namespace App\Repositories;

use App\Models\CovidCase;

interface CaseUpdateNotificationRepository
{
    /**
     * Fetch pairing code for the given case.
     *
     * @param CovidCase $case The case to export.
     */
    public function notify(CovidCase $case): bool;
}
