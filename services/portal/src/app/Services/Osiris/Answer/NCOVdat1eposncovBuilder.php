<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;
use App\Repositories\TestResultRepository;
use MinVWS\DBCO\Enum\Models\TestResultType;

use function assert;

class NCOVdat1eposncovBuilder extends AbstractSingleValueBuilder
{
    public function __construct(private readonly TestResultRepository $testResultRepository)
    {
    }

    protected function getValue(EloquentCase $case): ?string
    {
        assert(isset($case->test));
        assert($case->test instanceof Test);

        $minDate = Utils::minDate(
            $case->test->dateOfResult,
            $this->testResultRepository->firstPositiveResultByTypeForCase($case, TestResultType::lab())?->dateOfResult,
            $case->test->selfTestLabTestDate,
        );

        if ($minDate === null) {
            return null;
        }

        return Utils::formatDate($minDate);
    }
}
