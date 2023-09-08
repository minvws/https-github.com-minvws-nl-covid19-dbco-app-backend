<?php

declare(strict_types=1);

namespace App\Services\Osiris;

use App\Models\Enums\Osiris\CaseExportType;
use App\Schema\Validation\ValidationRule;
use App\Services\Osiris\Strategy\ExportableCaseProvider;

final readonly class SendInitialAnswersStrategy implements OsirisCaseExportStrategy
{
    public function __construct(
        private ExportableCaseProvider $provider,
        private OsirisCaseExporter $caseExporter,
    ) {
    }

    public function execute(string $caseUuid): void
    {
        $case = $this->provider->findValidCase($caseUuid, CaseExportType::INITIAL_ANSWERS, [ValidationRule::TAG_OSIRIS_INITIAL]);
        if ($case === null) {
            return;
        }

        $this->caseExporter->export($case, CaseExportType::INITIAL_ANSWERS);
    }

    public function supports(CaseExportType $caseExportType): bool
    {
        return $caseExportType === CaseExportType::INITIAL_ANSWERS;
    }
}
