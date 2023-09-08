<?php

declare(strict_types=1);

namespace App\Services\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use App\Exceptions\SkipTestResultImportException;
use App\Exceptions\TestReportingNotAllowedForOrganisationException;
use App\Repositories\OrganisationRepository;

class TestResultReportImportService implements TestResultReportImportServiceInterface
{
    public function __construct(
        private readonly OrganisationRepository $organisationRepository,
        private readonly IdentificationService $identificationService,
        private readonly TestResultImportService $testResultImportService,
        private readonly CaseImportService $caseImportService,
        private readonly TestResultAssignmentService $testResultAssignmentService,
    ) {
    }

    public function import(TestResultReport $testResultReport): void
    {
        if ($this->testResultImportService->isRetransmit($testResultReport->messageId)) {
            throw SkipTestResultImportException::messageAlreadyProcessed();
        }

        $organisation = $this->organisationRepository->getOrganisationByHpZoneCode($testResultReport->ggdIdentifier);
        if (!$organisation->isAllowedToReportTestResults) {
            throw new TestReportingNotAllowedForOrganisationException($organisation);
        }

        $pseudoBsn = $this->identificationService->identify($testResultReport, $organisation);
        $testResult = $this->testResultImportService->import($testResultReport, $organisation, $pseudoBsn);

        if ($pseudoBsn === null) {
            $this->caseImportService->importUnidentifiedCase($testResultReport, $testResult);
            return;
        }

        $case = $this->testResultAssignmentService->findCaseForAssignment($pseudoBsn);

        if ($case !== null) {
            $this->testResultAssignmentService->assignTestResultToCase($testResult, $case);
            $this->testResultAssignmentService->addRepeatResultLabel($case, $testResult);
            return;
        }

        $this->caseImportService->importIdentifiedCase($testResultReport, $testResult, $pseudoBsn);
    }
}
