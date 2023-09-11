<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Repositories\TestResultRepository;
use MinVWS\DBCO\Enum\Models\TestResultSource;

use function in_array;

class NCOVmonnrBuilder extends AbstractSingleValueBuilder
{
    public function __construct(
        private readonly TestResultRepository $testResultRepository,
    )
    {
    }

    /*
     * Wat is het monsternummer van de patiënt?
     */
    protected function getValue(EloquentCase $case): ?string
    {
        if ($case->source === TestResultSource::coronit()->value) {
            return null;
        }

        $latestPositiveTestResult = $this->testResultRepository->latestPositiveForCase($case);
        if ($latestPositiveTestResult === null) {
            return $case->test_monster_number;
        }

        if (
            !in_array(
                $latestPositiveTestResult->source,
                [TestResultSource::manual(), TestResultSource::meldportaal()],
                true,
            )
        ) {
            return null;
        }

        return $latestPositiveTestResult->monsterNumber;
    }
}
