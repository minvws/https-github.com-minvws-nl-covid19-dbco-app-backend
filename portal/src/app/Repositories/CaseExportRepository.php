<?php

namespace App\Repositories;

use App\Models\CovidCase;

interface CaseExportRepository
{
    /**
     * Fetch pairing code for the given case.
     *
     * @param CovidCase $case The case to export.
     */
    public function export(CovidCase $case): bool;
}
