<?php

declare(strict_types=1);

namespace Tests\Feature\Services\TestResult\Factories\Models;

use App\Dto\TestResultReport\TestResultReport;
use App\Dto\TestResultReport\TypeOfTest;
use App\Services\TestResult\Factories\Models\PersonFactory;
use App\Services\TestResult\Factories\Models\TestResultFactory;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\TestResultTypeOfTest;
use Tests\DataProvider\TestResultDataProvider;
use Tests\Feature\FeatureTestCase;

class TestResultFactoryTest extends FeatureTestCase
{
    public function testCreateTypeOfTest(): void
    {
        /** @var TypeOfTest $typeOfTest */
        $typeOfTest = $this->faker->randomElement(TypeOfTest::cases());

        $testResultReportData = TestResultDataProvider::payload();
        $testResultReportData['test']['typeOfTest'] = $typeOfTest->value;

        $testResultReport = TestResultReport::fromArray($testResultReportData);
        $organisation = $this->createOrganisation();
        $person = PersonFactory::create($testResultReport->person, null);

        $testResult = TestResultFactory::create($testResultReport, $organisation, $person);

        $this->assertEquals(TypeOfTest::toTestResultTypeOfTest($typeOfTest), $testResult->type_of_test);
    }

    public function testCreateWithoutTypeOfTest(): void
    {
        $testResultReportData = TestResultDataProvider::payload();
        $testResultReportData['test']['typeOfTest'] = null;

        $testResult = TestResultFactory::create(
            TestResultReport::fromArray($testResultReportData),
            $this->createOrganisation(),
            PersonFactory::create(TestResultReport::fromArray($testResultReportData)->person, null),
        );

        $this->assertEquals(TestResultTypeOfTest::unknown(), $testResult->type_of_test);
    }

    public function testCreateDateOfResult(): void
    {
        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $organisation = $this->createOrganisation();
        $person = PersonFactory::create($testResultReport->person, null);

        $testResult = TestResultFactory::create($testResultReport, $organisation, $person);

        $this->assertTrue(CarbonImmutable::createStrict(2021, 2, 1, 16, 30, 50)->equalTo($testResult->dateOfResult));
    }
}
