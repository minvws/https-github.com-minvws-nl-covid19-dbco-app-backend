<?php

declare(strict_types=1);

namespace App\Services\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use App\Models\Eloquent\EloquentCase;

class CaseLabelBinder
{
    public function __construct(
        public readonly CaseLabelService $caseLabelService,
    ) {
    }

    public function bind(EloquentCase $case, TestResultReport $testResultReport): void
    {
        $labels = $this->caseLabelService->forTestResultReport($testResultReport);
        $case->caseLabels()->attach($labels->pluck('uuid'));
    }
}
