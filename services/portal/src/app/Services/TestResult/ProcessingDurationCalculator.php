<?php

declare(strict_types=1);

namespace App\Services\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use Carbon\CarbonImmutable;
use LogicException;

use function round;

final class ProcessingDurationCalculator
{
    public function diffInSecondsSinceReceived(TestResultReport $testResultReport): float
    {
        $now = CarbonImmutable::now();
        $receivedAt = $testResultReport->receivedAt;

        if ($receivedAt > $now) {
            throw new LogicException('Expected received date to be in the past');
        }

        $diffInSeconds = $now->floatDiffInSeconds($receivedAt);
        return round($diffInSeconds, 2);
    }
}
