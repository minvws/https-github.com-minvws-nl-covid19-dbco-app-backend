<?php

declare(strict_types=1);

namespace App\Services\TestResult;

use App\Dto\TestResultReport\TestResultReport;

interface TestResultReportImportServiceInterface
{
    public function import(TestResultReport $testResultReport): void;
}
