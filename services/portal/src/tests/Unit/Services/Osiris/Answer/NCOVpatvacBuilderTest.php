<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\CovidCaseV3;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVpatvacBuilder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVpatvacBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVpatvacBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testYes(): void
    {
        $case = $this->createCase(YesNoUnknown::yes());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpatvac', 'J'));
    }

    public function testNull(): void
    {
        $case = $this->createCase(null);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpatvac', 'Onb'));
    }

    private function createCase(?YesNoUnknown $isVaccinated): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof CovidCaseV3);
        $case->createdAt = CarbonImmutable::now();
        $case->vaccination->isVaccinated = $isVaccinated;
        return $case;
    }
}
