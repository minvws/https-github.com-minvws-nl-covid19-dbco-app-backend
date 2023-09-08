<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\Test\TestV2;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVherinfmeldnrBuilder;
use Carbon\CarbonImmutable;
use Generator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVherinfmeldnrBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVherinfmeldnrBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testPreviousInfectionHpzoneNumber(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), YesNoUnknown::yes(), YesNoUnknown::yes(), '123456');
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVherinfmeldnr', '123456'));
    }

    public function testPreviousInfectionHpzoneNumberNull(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), YesNoUnknown::yes(), YesNoUnknown::yes(), null);
        $this->answersForCase($case)->assertEmpty();
    }

    public static function valuesProvider(): Generator
    {
        $values = [YesNoUnknown::no(), YesNoUnknown::unknown(), null];
        foreach ($values as $isReinfection) {
            foreach ($values as $previousInfectionReported) {
                foreach ($values as $previousInfectionProven) {
                    yield [$isReinfection, $previousInfectionReported, $previousInfectionProven];
                }
            }
        }
    }

    #[DataProvider('valuesProvider')]
    public function testValuesThatShouldResultInEmptyAnswerList(
        ?YesNoUnknown $isReinfection,
        ?YesNoUnknown $previousInfectionReported,
        ?YesNoUnknown $previousInfectionProven,
    ): void {
        $case = $this->createCase($isReinfection, $previousInfectionReported, $previousInfectionProven, '123456');
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(
        ?YesNoUnknown $isReinfection,
        ?YesNoUnknown $previousInfectionReported,
        ?YesNoUnknown $previousInfectionProven,
        ?string $previousInfectionHpzoneNumber,
    ): EloquentCase {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        assert(isset($case->test));
        assert($case->test instanceof TestV2);
        $case->test->isReinfection = $isReinfection;
        $case->test->previousInfectionReported = $previousInfectionReported;
        $case->test->previousInfectionProven = $previousInfectionProven;
        $case->test->previousInfectionHpzoneNumber = $previousInfectionHpzoneNumber;
        return $case;
    }
}
