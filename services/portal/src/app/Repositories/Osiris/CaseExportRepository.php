<?php

declare(strict_types=1);

namespace App\Repositories\Osiris;

use App\Dto\Osiris\Repository\CaseExportResult;
use App\Exceptions\Osiris\CaseExport\CaseExportException;
use App\Exceptions\Osiris\CaseExport\CaseExportRejectedException;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;

interface CaseExportRepository
{
    /**
     * @throws CaseExportException
     * @throws CaseExportRejectedException
     */
    public function exportCase(EloquentCase $case, CaseExportType $caseExportType): CaseExportResult;
}
