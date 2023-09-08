<?php

declare(strict_types=1);

namespace App\Services\Osiris;

use App\Events\Osiris\CaseExportFailed;
use App\Events\Osiris\CaseExportRejected;
use App\Events\Osiris\CaseExportSucceeded;
use App\Events\Osiris\CaseNotExportable;
use App\Exceptions\Osiris\CaseExport\CaseExportException;
use App\Exceptions\Osiris\CaseExport\CaseExportRejectedException;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use App\Repositories\Osiris\CaseExportRepository;

final class OsirisCaseExporter
{
    public function __construct(
        private readonly CaseExportRepository $caseExportRepository,
    ) {
    }

    /**
     * @throws CaseExportException
     * @throws CaseExportRejectedException
     */
    public function export(EloquentCase $case, CaseExportType $caseExportType): void
    {
        if ($case->hpzoneNumber !== null) {
            CaseNotExportable::dispatch($case, $caseExportType);

            return;
        }

        try {
            $result = $this->caseExportRepository->exportCase($case, $caseExportType);
        } catch (CaseExportRejectedException $rejectedException) {
            CaseExportRejected::dispatch($case, $caseExportType, $rejectedException->errors);

            throw $rejectedException;
        } catch (CaseExportException $clientException) {
            CaseExportFailed::dispatch($case, $caseExportType);

            throw $clientException;
        }

        CaseExportSucceeded::dispatch($case, $result, $caseExportType);
    }
}
