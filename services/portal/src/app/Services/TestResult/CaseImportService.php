<?php

declare(strict_types=1);

namespace App\Services\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use App\Helpers\Config;
use App\Jobs\ExportCaseToOsiris;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\TestResult;
use App\Models\Enums\Osiris\CaseExportType;
use App\Models\Metric\Osiris\CaseCreationToForwardingDuration;
use App\Models\Metric\TestResult\CaseCreated;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Repositories\CaseLabelRepository;
use App\Repositories\CaseRepository;
use App\Services\MetricService;
use App\Services\TestResult\Factories\Models\CovidCase\CovidCaseFactory;
use Carbon\CarbonImmutable;
use DBCO\Shared\Application\Metrics\Events\AbstractEvent;
use DBCO\Shared\Application\Metrics\Events\CreatedEvent;
use MinVWS\DBCO\Metrics\Services\EventService;
use Psr\Log\LoggerInterface;

final class CaseImportService
{
    public function __construct(
        private readonly CaseRepository $caseRepository,
        private readonly CaseLabelRepository $caseLabelRepository,
        private readonly EventService $eventService,
        private readonly MetricService $metricService,
        private readonly TestResultAssignmentService $testResultAssignmentService,
        private readonly LoggerInterface $logger,
        private readonly CaseLabelBinder $caseLabelBinder,
    ) {
    }

    public function importIdentifiedCase(
        TestResultReport $testResultReport,
        TestResult $testResult,
        PseudoBsn $pseudoBsn,
    ): EloquentCase {
        $case = $this->importCase($testResultReport, $testResult, $pseudoBsn);
        $this->metricService->measure(CaseCreated::identified());
        return $case;
    }

    public function importUnidentifiedCase(TestResultReport $testResultReport, TestResult $testResult): EloquentCase
    {
        $case = $this->importCase($testResultReport, $testResult, null);
        $caseLabel = $this->caseLabelRepository->getLabelByCode(CaseLabelRepository::CASE_LABEL_CODE_NOT_IDENTIFIED);
        $this->caseRepository->addCaseLabel($case, $caseLabel);
        $this->metricService->measure(CaseCreated::notIdentified());
        return $case;
    }

    private function importCase(
        TestResultReport $testResultReport,
        TestResult $testResult,
        ?PseudoBsn $pseudoBsn,
    ): EloquentCase {
        $this->metricService->measure(new CaseCreationToForwardingDuration(
            CarbonImmutable::instance($testResultReport->receivedAt)->diffInSeconds(),
        ));
        $case = CovidCaseFactory::create($testResultReport, $testResult->organisation, $pseudoBsn);
        $this->caseRepository->save($case);
        $this->eventService->registerEvent(new CreatedEvent(AbstractEvent::ACTOR_SYSTEM, $case->uuid));
        $this->logger->info('Stored case', ['caseUuid' => $case->uuid, 'messageId' => $testResultReport->messageId]);
        $this->testResultAssignmentService->assignTestResultToCase($testResult, $case);
        $this->caseLabelBinder->bind($case, $testResultReport);

        if (!Config::boolean('misc.test_result.simulation_mode_enabled')) {
            ExportCaseToOsiris::dispatchIfEnabled($case->uuid, CaseExportType::INITIAL_ANSWERS);
        }

        return $case;
    }
}
