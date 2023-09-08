<?php

declare(strict_types=1);

namespace App\Services\Osiris\Strategy;

use App\Events\Osiris\CaseValidationRaisesNotice;
use App\Events\Osiris\CaseValidationRaisesWarning;
use App\Events\Osiris\ExportableCaseNotFound;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use App\Repositories\CaseRepository;
use App\Schema\Validation\ValidationRules;
use App\Services\CaseFragmentsValidationService;
use Throwable;

use function array_key_exists;

final class ExportableCaseProvider
{
    public function __construct(
        private readonly CaseRepository $caseRepository,
        private readonly CaseFragmentsValidationService $caseValidator,
    ) {
    }

    /**
     * @param array<int,string> $validationRuleTags
     */
    public function findValidCase(string $caseUuid, CaseExportType $caseExportType, array $validationRuleTags): ?EloquentCase
    {
        $case = $this->caseRepository->getCaseByUuid($caseUuid);

        if ($case === null) {
            ExportableCaseNotFound::dispatch();

            return null;
        }

        try {
            $validationResult = $this->caseValidator->validateAllFragments($case, $validationRuleTags);
        } catch (Throwable) {
            return null;
        }

        if ($this->containsSeverity($validationResult, ValidationRules::WARNING)) {
            CaseValidationRaisesWarning::dispatch($case, $validationResult, $caseExportType);

            return null;
        }

        if ($this->containsSeverity($validationResult, ValidationRules::NOTICE)) {
            CaseValidationRaisesNotice::dispatch($case, $validationResult, $caseExportType);
        }

        return $case;
    }

    private function containsSeverity(array $validationResult, string $severity): bool
    {
        foreach ($validationResult as $fragmentValidationResult) {
            if (array_key_exists($severity, $fragmentValidationResult)) {
                return true;
            }
        }

        return false;
    }
}
