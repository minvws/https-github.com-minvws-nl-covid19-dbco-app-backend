<?php

declare(strict_types=1);

namespace Tests\Unit\Services\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use App\Services\TestResult\ProcessingDurationCalculator;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use LogicException;
use Tests\DataProvider\TestResultDataProvider;
use Tests\Unit\UnitTestCase;

final class ProcessingDurationCalculatorTest extends UnitTestCase
{
    public function testDiffInSecondsSinceReceived(): void
    {
        $receivedAt = '2022-12-05 12:55:20.118024';
        $now = '2022-12-05 12:55:30.777972';
        $expectedDiffInSeconds = 10.66;

        CarbonImmutable::setTestNow($now);
        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $testResultReport->receivedAt = new DateTimeImmutable($receivedAt);

        $calculator = new ProcessingDurationCalculator();
        $actual = $calculator->diffInSecondsSinceReceived($testResultReport);

        $this->assertSame($expectedDiffInSeconds, $actual);
    }

    public function testExceptionThrownWhenReceivedDateIsNotInThePast(): void
    {
        $receivedAt = '2022-11-23 14:00:30';
        $now = '2022-11-23 14:00:00';

        CarbonImmutable::setTestNow($now);
        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $testResultReport->receivedAt = new DateTimeImmutable($receivedAt);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Expected received date to be in the past');

        $calculator = new ProcessingDurationCalculator();
        $calculator->diffInSecondsSinceReceived($testResultReport);
    }
}
