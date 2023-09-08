<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Models;

use App\Dto\TestResultReport\TestResultReport;
use App\Dto\TestResultReport\TypeOfTest;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\Person;
use App\Models\Eloquent\TestResult;
use App\Services\TestResult\Factories\Enums\TestResultSourceFactory;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\TestResultType;

final class TestResultFactory
{
    public static function create(
        TestResultReport $testResultReport,
        EloquentOrganisation $organisation,
        Person $person,
    ): TestResult {
        /** @var TestResult $testResult */
        $testResult = TestResult::getSchema()->getCurrentVersion()->newInstance();
        $testResult->organisation()->associate($organisation);
        $testResult->person()->associate($person);
        $testResult->dateOfTest = CarbonImmutable::parse($testResultReport->test->sampleDate->format('Y-m-d'));

        if ($testResultReport->triage->dateOfFirstSymptom instanceof DateTimeInterface) {
            $testResult->dateOfSymptomOnset = CarbonImmutable::parse(
                $testResultReport->triage->dateOfFirstSymptom->format('Y-m-d'),
            );
        }

        $testResult->messageId = $testResultReport->messageId;
        $testResult->general = GeneralFactory::create($testResultReport->test);
        $testResult->monsterNumber = $testResultReport->test->sampleId;
        $testResult->type = TestResultType::lab();
        $testResult->source = TestResultSourceFactory::fromSource($testResultReport->test->source);
        $testResult->sourceId = $testResultReport->test->sampleId;
        $testResult->receivedAt = $testResultReport->receivedAt;
        $testResult->setTypeOfTest(TypeOfTest::toTestResultTypeOfTest($testResultReport->test->typeOfTest), null);
        $testResult->dateOfResult = $testResultReport->test->resultDate;
        $testResult->sample_location = $testResultReport->test->sampleLocation;

        return $testResult;
    }
}
