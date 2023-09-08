<?php

declare(strict_types=1);

namespace App\Services\TestResult;

use App\Http\Controllers\Api\Case\TestResult\CreateManualTest;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\TestResult;
use App\Repositories\TestResultRepository;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Collection;
use MinVWS\DBCO\Enum\Models\TestResultSource;

class TestResultService
{
    public function __construct(
        private readonly TestResultRepository $testResultRepository,
    ) {
    }

    /**
     * @return Collection<int, TestResult>
     */
    public function getByCase(EloquentCase $eloquentCase): Collection
    {
        return $this->testResultRepository->getByCaseUuid($eloquentCase->uuid);
    }

    public function createManualTestResult(EloquentCase $case, CreateManualTest $createManualTest): TestResult
    {
        /** @var TestResult $testResult */
        $testResult = TestResult::getSchema()->getCurrentVersion()->newInstance();
        $testResult->organisation()->associate($case->organisation);
        $testResult->covidCase()->associate($case);

        $testResult->source = TestResultSource::manual();
        $testResult->receivedAt = new DateTimeImmutable();

        $testResult->type = $createManualTest->typeOfTest->type;
        $testResult->setTypeOfTest($createManualTest->typeOfTest, $createManualTest->customTypeOfTest);
        $testResult->dateOfTest = $createManualTest->dateOfTest;
        $testResult->result = $createManualTest->result;
        $testResult->monsterNumber = $createManualTest->monsterNumber;
        $testResult->laboratory = $createManualTest->laboratory;

        $this->testResultRepository->save($testResult);
        return $testResult;
    }

    public function getByUuid(string $testResultUuid): TestResult
    {
        return $this->testResultRepository->getByUuid($testResultUuid);
    }

    /**
     * @throws TestResultNotDeletable
     */
    public function deleteByUuid(string $testResultUuid, EloquentCase $case): void
    {
        $testResult = $this->getByUuid($testResultUuid);

        if ($testResult->covidCase === null || $case->uuid !== $testResult->covidCase->uuid) {
            throw TestResultNotDeletable::caseMismatch();
        }
        if ($testResult->source->value !== TestResultSource::manual()->value) {
            throw TestResultNotDeletable::incorrectSource();
        }

        $testResult->delete();
    }
}
