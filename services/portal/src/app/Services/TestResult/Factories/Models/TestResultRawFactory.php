<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Models;

use App\Dto\TestResultReport\TestResultReport;
use App\Models\Eloquent\TestResult;
use App\Models\Eloquent\TestResultRaw;

final class TestResultRawFactory
{
    public static function create(TestResultReport $testResultReport, TestResult $testResult): TestResultRaw
    {
        /** @var TestResultRaw $testResultRaw */
        $testResultRaw = TestResultRaw::getSchema()->getCurrentVersion()->newInstance();

        $testResultRaw->testResult()->associate($testResult);
        $testResultRaw->data = $testResultReport->raw;

        return $testResultRaw;
    }
}
