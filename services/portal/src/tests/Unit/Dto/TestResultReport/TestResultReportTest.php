<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\TestResultReport;

use App\Dto\TestResultReport\TestResultReport;
use DateTimeImmutable;
use RuntimeException;
use Tests\DataProvider\TestResultDataProvider;
use Tests\Unit\UnitTestCase;

final class TestResultReportTest extends UnitTestCase
{
    public function testMapDateOfFirstSymptomWithDateString(): void
    {
        $payload = TestResultDataProvider::payload();
        $payload['triage']['dateOfFirstSymptom'] = '12-20-2022';
        $testResultReport = TestResultReport::fromArray($payload);

        $expected = DateTimeImmutable::createFromFormat('m-d-Y', '12-20-2022');
        $this->assertEquals($expected, $testResultReport->triage->dateOfFirstSymptom);
    }

    public function testMapDateOfFirstSymptomWithNullValue(): void
    {
        $payload = TestResultDataProvider::payload();
        $payload['triage']['dateOfFirstSymptom'] = null;
        $testResultReport = TestResultReport::fromArray($payload);

        $this->assertNull($testResultReport->triage->dateOfFirstSymptom);
    }

    public function testMapDateOfFirstSymptomWithInvalidDateString(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to parse dateOfFirstSymptom');

        $payload = TestResultDataProvider::payload();
        $payload['triage']['dateOfFirstSymptom'] = 'foo';
        TestResultReport::fromArray($payload);
    }
}
