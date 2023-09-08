<?php

declare(strict_types=1);

namespace App\Services\TestResult;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\TestResult;
use App\Models\Metric\TestResult\TestResultToCovidCaseAssignment;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Repositories\CaseLabelRepository;
use App\Repositories\CaseRepository;
use App\Repositories\TestResultRepository;
use App\Services\MetricService;
use Carbon\CarbonInterval;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use Psr\Log\LoggerInterface;

use function config;
use function today;

final class TestResultAssignmentService
{
    public function __construct(
        private readonly CaseLabelRepository $caseLabelRepository,
        private readonly CaseRepository $caseRepository,
        private readonly TestResultRepository $testResultRepository,
        private readonly LoggerInterface $logger,
        private readonly MetricService $metricService,
    ) {
    }

    public function findCaseForAssignment(PseudoBsn $pseudoBsn): ?EloquentCase
    {
        $assignmentPeriodInWeeks = config('misc.test_result.covid_case_assignment_period_in_weeks');
        $createdAfterDate = today()->sub(CarbonInterval::weeks($assignmentPeriodInWeeks));

        return $this->caseRepository->findCaseByPseudoBsnGuidCreatedAfter($pseudoBsn->getGuid(), $createdAfterDate);
    }

    public function assignTestResultToCase(TestResult $testResult, EloquentCase $case): void
    {
        $this->testResultRepository->addCase($testResult, $case);

        $case->bcoStatus = BCOStatus::draft();
        $this->caseRepository->save($case);

        $this->logger->info(
            'Assigned test result to case',
            [
                'testResultUuid' => $testResult->uuid,
                'caseUuid' => $case->uuid,
                'messageId' => $testResult->messageId,
            ],
        );

        $this->metricService->measure(
            $case->wasRecentlyCreated
                ? TestResultToCovidCaseAssignment::newCase()
                : TestResultToCovidCaseAssignment::existingCase(),
        );
    }

    public function addRepeatResultLabel(EloquentCase $case, TestResult $testResult): void
    {
        $caseLabelRepeatResult = $this->caseLabelRepository->getLabelByCode(CaseLabelRepository::CASE_LABEL_REPEAT_RESULT);

        $case->caseLabels()->syncWithoutDetaching($caseLabelRepeatResult);
        $case->bcoStatus = BCOStatus::draft();
        $this->caseRepository->save($case);

        $this->logger->info(
            'Added "repeat result" label to case',
            [
                'caseUuid' => $case->uuid,
                'testResultUuid' => $testResult->uuid,
                'messageId' => $testResult->messageId,
            ],
        );
    }
}
