<?php

declare(strict_types=1);

namespace Tests\Feature\Models\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use App\Models\Eloquent\CaseLabel;
use App\Models\Eloquent\TestResult;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Repositories\CaseLabelRepository;
use App\Services\TestResult\CaseImportService;
use App\Services\TestResult\Factories\Enums\CareTestLocationCategory;
use App\Services\TestResult\IdentificationService;
use Tests\DataProvider\TestResultDataProvider;
use Tests\Feature\FeatureTestCase;

class CaseLabelBinderTest extends FeatureTestCase
{
    public function testItBindsTheCareLabelWhenTestLocationCategoryIsCareLocation(): void
    {
        $testResult = $this->createTestResult();
        $testResultReport = $this->getTestResultReportWithTestLocationCategory(
            $this->faker->randomElement(CareTestLocationCategory::cases())->value,
        );
        $pseudoBsn = $this->getPseudoBsn($testResultReport);

        $this->assertDatabaseHas(
            'case_case_label',
            $this->caseCaseLabelRelation($testResultReport, $testResult, $pseudoBsn),
        );
    }

    public function testItDoesNotBindTheCareLabelWhenTestLocationCategoryIsNotCareLocation(): void
    {
        $testResult = $this->createTestResult();
        $testResultReport = $this->getTestResultReportWithTestLocationCategory($this->faker->word());
        $pseudoBsn = $this->getPseudoBsn($testResultReport);

        $this->assertDatabaseMissing(
            'case_case_label',
            $this->caseCaseLabelRelation($testResultReport, $testResult, $pseudoBsn),
        );
    }

    public function testItBindsTheCareLabelWhenTestLocationCategoryIsCareLocationForUnidentifiedCases(): void
    {
        $testResult = $this->createTestResult();
        $testResultReport = $this->getTestResultReportWithTestLocationCategory(
            $this->faker->randomElement(CareTestLocationCategory::cases())->value,
        );
        $pseudoBsn = $this->getPseudoBsn($testResultReport);

        $this->assertDatabaseHas(
            'case_case_label',
            $this->caseCaseLabelRelation($testResultReport, $testResult, $pseudoBsn, 'importUnIdentifiedCase'),
        );
    }

    public function testItDoesNotThrowAnExceptionWhenTestLocationCategoryIsNull(): void
    {
        $testResult = $this->createTestResult();
        $testResultReport = $this->getTestResultReportWithTestLocationCategory(null);
        $pseudoBsn = $this->getPseudoBsn($testResultReport);

        $this->assertDatabaseMissing(
            'case_case_label',
            $this->caseCaseLabelRelation($testResultReport, $testResult, $pseudoBsn, 'importUnIdentifiedCase'),
        );
    }

    public function testItDoesNotBindTheCareLabelWhenTestLocationCategoryIsNotCareLocationForUnidentifiedCases(): void
    {
        $testResult = $this->createTestResult();
        $testResultReport = $this->getTestResultReportWithTestLocationCategory($this->faker->word());
        $pseudoBsn = $this->getPseudoBsn($testResultReport);

        $this->assertDatabaseMissing(
            'case_case_label',
            $this->caseCaseLabelRelation($testResultReport, $testResult, $pseudoBsn, 'importUnIdentifiedCase'),
        );
    }

    private function caseCaseLabelRelation(
        TestResultReport $testResultReport,
        TestResult $testResult,
        PseudoBsn $pseudoBsn,
        string $method = 'importIdentifiedCase',
    ): array {
        /** @var CaseImportService $importService */
        $importService = $this->app->get(CaseImportService::class);

        /** @var CaseLabel $caseLabel */
        $caseLabel = CaseLabel::query()
            ->where('code', '=', CaseLabelRepository::CASE_LABEL_HEALTHCARE)
            ->firstOrFail();

        return [
            'case_uuid' => $importService->$method($testResultReport, $testResult, $pseudoBsn)->uuid,
            'case_label_uuid' => $caseLabel->uuid,
        ];
    }

    private function getPseudoBsn(TestResultReport $testResultReport): mixed
    {
        /** @var IdentificationService $identificationService */
        $identificationService = $this->app->get(IdentificationService::class);

        return $identificationService->identify($testResultReport, $this->createOrganisation());
    }

    private function getTestResultReportWithTestLocationCategory(?string $careTestLocationCategory): TestResultReport
    {
        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());
        $testResultReport->test->testLocationCategory = $careTestLocationCategory;
        return $testResultReport;
    }
}
