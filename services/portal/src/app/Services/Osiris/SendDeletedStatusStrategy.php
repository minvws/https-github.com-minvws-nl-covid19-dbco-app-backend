<?php

declare(strict_types=1);

namespace App\Services\Osiris;

use App\Events\Osiris\ExportableCaseNotFound;
use App\Models\Enums\Osiris\CaseExportType;
use App\Repositories\CaseRepository;

final readonly class SendDeletedStatusStrategy implements OsirisCaseExportStrategy
{
    public function __construct(
        private CaseRepository $caseRepository,
        private OsirisCaseExporter $caseExporter,
    ) {
    }

    public function execute(string $caseUuid): void
    {
        $case = $this->caseRepository->getCaseIncludingSoftDeletes($caseUuid);

        if ($case === null) {
            ExportableCaseNotFound::dispatch();

            return;
        }

        $this->caseExporter->export($case, CaseExportType::DELETED_STATUS);
    }

    public function supports(CaseExportType $caseExportType): bool
    {
        return $caseExportType === CaseExportType::DELETED_STATUS;
    }
}
