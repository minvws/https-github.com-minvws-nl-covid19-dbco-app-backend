<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Models\CovidCase;

use App\Dto\TestResultReport\TestResultReport;
use App\Dto\TestResultReport\TypeOfTest;
use App\Models\CovidCase\Test;
use App\Models\Versions\CovidCase\Test\TestV4;
use App\Services\TestResult\Factories\Enums\TestResultSourceFactory;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\LabTestIndicator;
use MinVWS\DBCO\Enum\Models\SelfTestIndicator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

final class TestFactory
{
    public static function create(TestResultReport $testResultReport): TestV4
    {
        /** @var TestV4 $test */
        $test = Test::getSchema()->getVersion(4)->newInstance();
        $test->dateOfTest = CarbonImmutable::parse($testResultReport->test->sampleDate->format('Y-m-d'));
        $test->dateOfResult = CarbonImmutable::parse($testResultReport->test->resultDate->format('Y-m-d'));

        if ($testResultReport->triage->dateOfFirstSymptom instanceof DateTimeInterface) {
            $test->dateOfSymptomOnset = CarbonImmutable::parse($testResultReport->triage->dateOfFirstSymptom->format('Y-m-d'));
        }

        // We can't decide this based on the incoming data, could be calculated based on previous infections for index
        $test->isReinfection = YesNoUnknown::unknown();

        $test->monsterNumber = $testResultReport->test->sampleId;
        $test->source = TestResultSourceFactory::fromSource($testResultReport->test->source)->value;
        $test->testLocation = $testResultReport->test->testLocation;
        $test->testLocationCategory = $testResultReport->test->testLocationCategory;

        switch ($testResultReport->test->typeOfTest) {
            case TypeOfTest::SELF_TEST:
                $test->infectionIndicator = InfectionIndicator::selfTest();
                $test->selfTestIndicator = SelfTestIndicator::unknown();
                break;
            case TypeOfTest::LAB_TEST_PCR:
                $test->infectionIndicator = InfectionIndicator::labTest();
                $test->labTestIndicator = LabTestIndicator::molecular();
                break;
            case TypeOfTest::LAB_TEST_ANTIGEN:
                $test->infectionIndicator = InfectionIndicator::labTest();
                $test->labTestIndicator = LabTestIndicator::antigen();
                break;
            default:
                $test->infectionIndicator = InfectionIndicator::unknown();
                break;
        }

        return $test;
    }
}
