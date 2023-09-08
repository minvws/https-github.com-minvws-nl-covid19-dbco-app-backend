<?php

declare(strict_types=1);

namespace App\Services\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\TestResult;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Repositories\TestResultRepository;
use App\Services\TestResult\Factories\Models\PersonFactory;
use App\Services\TestResult\Factories\Models\TestResultFactory;
use App\Services\TestResult\Factories\Models\TestResultRawFactory;
use MinVWS\DBCO\Enum\Models\TestResultResult;
use Psr\Log\LoggerInterface;

final class TestResultImportService
{
    public function __construct(
        private readonly TestResultRepository $testResultRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function isRetransmit(string $messageId): bool
    {
        return $this->testResultRepository->hasMessageId($messageId);
    }

    public function import(
        TestResultReport $testResultReport,
        EloquentOrganisation $organisation,
        ?PseudoBsn $pseudoBsn,
    ): TestResult {
        $person = PersonFactory::create($testResultReport->person, $pseudoBsn);
        $person->save();

        $testResult = TestResultFactory::create($testResultReport, $organisation, $person);
        // All Test Results that are imported via the Gateway are positive tests.
        $testResult->result = TestResultResult::positive();
        $testResult->save();

        $testResultRaw = TestResultRawFactory::create($testResultReport, $testResult);
        $testResultRaw->save();

        $this->logger->info(
            'Stored test result',
            [
                'testResultUuid' => $testResult->uuid,
                'messageId' => $testResultReport->messageId,
            ],
        );

        return $testResult;
    }
}
