<?php

declare(strict_types=1);

namespace App\Services\Osiris;

use App\Exceptions\Osiris\CaseExport\CaseExportExceptionInterface;
use App\Models\Enums\Osiris\CaseExportType;

interface OsirisCaseExportStrategy
{
    /**
     * @throws CaseExportExceptionInterface
     */
    public function execute(string $caseUuid): void;

    public function supports(CaseExportType $caseExportType): bool;
}
