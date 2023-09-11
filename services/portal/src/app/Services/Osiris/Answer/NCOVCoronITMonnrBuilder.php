<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Repositories\TestResultRepository;
use MinVWS\DBCO\Enum\Models\TestResultSource;

class NCOVCoronITMonnrBuilder extends AbstractSingleValueBuilder
{
    public function __construct(
        private readonly TestResultRepository $testResultRepository,
    )
    {
    }

    protected function getValue(EloquentCase $case): ?string
    {
        $latestPositiveTest = $this->testResultRepository->latestPositiveForCase($case);

        if ($latestPositiveTest?->source === TestResultSource::coronit()) {
            return $latestPositiveTest->monsterNumber;
        }

        if ($case->source !== TestResultSource::coronit()->value) {
            return null;
        }

        return $case->test_monster_number;
    }
}
