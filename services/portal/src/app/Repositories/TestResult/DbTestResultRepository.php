<?php

declare(strict_types=1);

namespace App\Repositories\TestResult;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\TestResult;
use App\Repositories\TestResultRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MinVWS\DBCO\Enum\Models\TestResultType;
use Webmozart\Assert\Assert;

final class DbTestResultRepository implements TestResultRepository
{
    public function hasMessageId(string $messageId): bool
    {
        return TestResult::where('message_id', $messageId)->exists();
    }

    public function addCase(TestResult $testResult, EloquentCase $case): void
    {
        $testResult->covidCase()->associate($case);
        $testResult->save();
    }

    public function getByCaseUuid(string $caseUuid): Collection
    {
        return TestResult::query()
            ->where('case_uuid', $caseUuid)
            ->orderBy('date_of_test', 'desc')
            ->get();
    }

    public function save(TestResult $testResult): void
    {
        $testResult->save();
    }

    public function getByUuid(string $testResultUuid): TestResult
    {
        /** @var TestResult $testResult */
        $testResult = TestResult::whereUuid($testResultUuid)->firstOrFail();

        return $testResult;
    }

    public function latestPositiveForCase(EloquentCase $case): ?TestResult
    {
        $latestTestResult = $this->getPositiveResultsQuery($case)
            ->orderByDesc('date_of_test')
            ->first();

        Assert::nullOrIsInstanceOf($latestTestResult, TestResult::class);
        return $latestTestResult;
    }

    public function firstPositiveResultByTypeForCase(EloquentCase $case, TestResultType $testResultType): ?TestResult
    {
        $firstTestResult = $this->getPositiveResultsQuery($case)
            ->orderBy('date_of_test')
            ->where('type', $testResultType)
            ->first();

        Assert::nullOrIsInstanceOf($firstTestResult, TestResult::class);
        return $firstTestResult;
    }

    private function getPositiveResultsQuery(EloquentCase $case): HasMany
    {
        return $case->testResults()
            //Have to call reorder because a default orderby is set on the relation
            ->reorder()
            ->where('result', \MinVWS\DBCO\Enum\Models\TestResult::positive());
    }
}
