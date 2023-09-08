<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVherinfV2Builder;
use Carbon\CarbonImmutable;
use Generator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function array_merge;
use function assert;

#[Builder(NCOVherinfV2Builder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVherinfV2BuilderTest extends TestCase
{
    use AssertAnswers;

    public function testIsReinfectionYes(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), YesNoUnknown::yes(), YesNoUnknown::yes());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVherinfV2', '1'));

        $case = $this->createCase(YesNoUnknown::yes(), YesNoUnknown::yes(), YesNoUnknown::no());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVherinfV2', '6'));

        $case = $this->createCase(YesNoUnknown::yes(), YesNoUnknown::no(), YesNoUnknown::yes());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVherinfV2', '5'));

        $case = $this->createCase(YesNoUnknown::yes(), YesNoUnknown::no(), YesNoUnknown::no());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVherinfV2', '4'));
    }

    public static function previousInfectionProvider(): Generator
    {
        $values = array_merge(YesNoUnknown::all(), [null]);
        foreach ($values as $previousInfectionReported) {
            foreach ($values as $previousInfectionProven) {
                yield [$previousInfectionReported, $previousInfectionProven];
            }
        }
    }

    #[DataProvider('previousInfectionProvider')]
    public function testIsReinfectionNo(?YesNoUnknown $previousInfectionReported, ?YesNoUnknown $previousInfectionProven): void
    {
        $case = $this->createCase(YesNoUnknown::no(), $previousInfectionReported, $previousInfectionProven);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVherinfV2', '3'));
    }

    #[DataProvider('previousInfectionProvider')]
    public function testIsReinfectionUnknown(?YesNoUnknown $previousInfectionReported, ?YesNoUnknown $previousInfectionProven): void
    {
        $case = $this->createCase(YesNoUnknown::unknown(), $previousInfectionReported, $previousInfectionProven);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVherinfV2', '4'));
    }

    #[DataProvider('previousInfectionProvider')]
    public function testIsReinfectionNull(?YesNoUnknown $previousInfectionReported, ?YesNoUnknown $previousInfectionProven): void
    {
        $case = $this->createCase(null, $previousInfectionReported, $previousInfectionProven);
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(
        ?YesNoUnknown $isReinfection,
        ?YesNoUnknown $previousInfectionReported,
        ?YesNoUnknown $previousInfectionProven,
    ): EloquentCase {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        assert(isset($case->test));
        assert($case->test instanceof Test);
        $case->test->isReinfection = $isReinfection;
        $case->test->previousInfectionReported = $previousInfectionReported;
        $case->test->previousInfectionProven = $previousInfectionProven;
        return $case;
    }
}
