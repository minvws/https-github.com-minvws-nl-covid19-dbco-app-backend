<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\TestResult;
use Illuminate\Database\Eloquent\Collection;
use MinVWS\DBCO\Enum\Models\TestResultType;

interface TestResultRepository
{
    public function hasMessageId(string $messageId): bool;

    public function addCase(TestResult $testResult, EloquentCase $case): void;

    public function getByCaseUuid(string $caseUuid): Collection;

    public function save(TestResult $testResult): void;

    public function getByUuid(string $testResultUuid): TestResult;

    public function latestPositiveForCase(EloquentCase $case): ?TestResult;

    public function firstPositiveResultByTypeForCase(EloquentCase $case, TestResultType $testResultType): ?TestResult;
}
