<?php

declare(strict_types=1);

namespace App\Services\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use App\Models\Eloquent\CaseLabel;
use App\Repositories\CaseLabelRepository;
use App\Services\TestResult\Factories\Enums\CareTestLocationCategory;
use App\Services\TestResult\Factories\Enums\SchoolTestLocationCategory;
use App\Services\TestResult\Factories\Enums\SocietalInstitutionTestLocationCategory;
use Illuminate\Support\Collection;

use function strtolower;

class CaseLabelService
{
    public function __construct(
        private readonly CaseLabelRepository $caseLabelRepository,
    ) {
    }

    /**
     * @return Collection<int,CaseLabel>
     */
    public function forTestResultReport(TestResultReport $report): Collection
    {
        /** @var Collection<int,CaseLabel> $labels */
        $labels = new Collection();

        $testLocationCategory = $report->test->testLocationCategory;

        if ($testLocationCategory === null) {
            return $labels;
        }

        $testLocationCategory = strtolower($testLocationCategory);

        if (CareTestLocationCategory::tryFrom($testLocationCategory) instanceof CareTestLocationCategory) {
            $labels[] = $this->caseLabelRepository->getLabelByCode(CaseLabelRepository::CASE_LABEL_HEALTHCARE);
        }

        if (SocietalInstitutionTestLocationCategory::tryFrom($testLocationCategory) instanceof SocietalInstitutionTestLocationCategory) {
            $labels[] = $this->caseLabelRepository->getLabelByCode(CaseLabelRepository::CASE_LABEL_SOCIETAL_INSTITUTION);
        }

        if (SchoolTestLocationCategory::tryFrom($testLocationCategory) instanceof SchoolTestLocationCategory) {
            $labels[] = $this->caseLabelRepository->getLabelByCode(CaseLabelRepository::CASE_LABEL_SCHOOL);
        }

        return $labels;
    }
}
