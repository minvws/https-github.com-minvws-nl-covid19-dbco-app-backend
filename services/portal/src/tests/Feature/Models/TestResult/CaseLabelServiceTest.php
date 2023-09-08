<?php

declare(strict_types=1);

namespace Tests\Feature\Models\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use App\Repositories\CaseLabelRepository;
use App\Services\TestResult\CaseLabelService;
use App\Services\TestResult\Factories\Enums\CareTestLocationCategory;
use App\Services\TestResult\Factories\Enums\SchoolTestLocationCategory;
use App\Services\TestResult\Factories\Enums\SocietalInstitutionTestLocationCategory;
use Tests\DataProvider\TestResultDataProvider;
use Tests\Feature\FeatureTestCase;

class CaseLabelServiceTest extends FeatureTestCase
{
    private CaseLabelService $caseLabelService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caseLabelService = $this->app->get(CaseLabelService::class);
    }

    public function testItReturnsTheCareLabelCorrectly(): void
    {
        /** @var CareTestLocationCategory $careTestLocationCategory */
        $careTestLocationCategory = $this->faker->randomElement(CareTestLocationCategory::cases());

        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $testResultReport->test->testLocationCategory = $careTestLocationCategory->value;
        $labels = $this->caseLabelService->forTestResultReport($testResultReport);

        $this->assertCount(1, $labels);
        $this->assertTrue($labels[0]->code === CaseLabelRepository::CASE_LABEL_HEALTHCARE);
    }

    public function testItReturnsTheSocietalInstitutionLabelCorrectly(): void
    {
        /** @var SocietalInstitutionTestLocationCategory $societalInstitutionTestLocationCategory */
        $societalInstitutionTestLocationCategory = $this->faker->randomElement(
            SocietalInstitutionTestLocationCategory::cases(),
        );

        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $testResultReport->test->testLocationCategory = $societalInstitutionTestLocationCategory->value;
        $labels = $this->caseLabelService->forTestResultReport($testResultReport);

        $this->assertCount(1, $labels);
        $this->assertTrue($labels[0]->code === CaseLabelRepository::CASE_LABEL_SOCIETAL_INSTITUTION);
    }

    public function testItReturnsTheSchoolLabelCorrectly(): void
    {
        /** @var SchoolTestLocationCategory $schoolTestLocationCategory */
        $schoolTestLocationCategory = $this->faker->randomElement(
            SchoolTestLocationCategory::cases(),
        );

        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $testResultReport->test->testLocationCategory = $schoolTestLocationCategory->value;
        $labels = $this->caseLabelService->forTestResultReport($testResultReport);

        $this->assertCount(1, $labels);
        $this->assertTrue($labels[0]->code === CaseLabelRepository::CASE_LABEL_SCHOOL);
    }

    public function testItDoesNotReturnNonExistentLabels(): void
    {
        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $testResultReport->test->testLocationCategory = 'foobarfakelabel';
        $labels = $this->caseLabelService->forTestResultReport($testResultReport);

        $this->assertCount(0, $labels);
    }
}
