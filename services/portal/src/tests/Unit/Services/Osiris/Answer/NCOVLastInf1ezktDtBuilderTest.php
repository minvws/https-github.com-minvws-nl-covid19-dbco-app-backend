<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVLastInf1ezktDtBuilder;
use App\Services\Osiris\Answer\Utils;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVLastInf1ezktDtBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVLastInf1ezktDtBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testFirstSickDay(): void
    {
        $case = $this->createCase(YesNoUnknown::yes());
        $this->answersForCase($case)->assertAnswer(
            new Answer('NCOVLastInf1ezktDt', Utils::formatDate($case->test->previousInfectionDateOfSymptom)),
        );
    }

    public function testUnknownFirstSickDay(): void
    {
        $case = $this->createCase(YesNoUnknown::no());
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(?YesNoUnknown $hasFirstSickDay): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);

        $case->createdAt = CarbonImmutable::now();
        $case->test->previousInfectionDateOfSymptom = $hasFirstSickDay === YesNoUnknown::yes() ? $this->faker->dateTimeBetween() : null;

        return $case;
    }
}
