<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\Pregnancy\PregnancyV2;
use App\Models\Versions\CovidCase\UnderlyingSuffering\UnderlyingSufferingV2Up;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVondaandcomorV2Builder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\UnderlyingSuffering;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;
use function count;

#[Builder(NCOVondaandcomorV2Builder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVondaandcomorV2BuilderTest extends TestCase
{
    use AssertAnswers;

    public function testAlive(): void
    {
        $case = $this->createCase(
            $this->faker->dateTimeBetween(startDate: '-70 years', endDate: '-50 years'),
            YesNoUnknown::yes(),
            [UnderlyingSuffering::cardioVascular()],
        );

        $this->answersForCase($case)->assertCount(1);
    }

    public function testOver70Years(): void
    {
        $case = $this->createCase(
            $this->faker->dateTimeBetween(startDate: '-80 years', endDate: '-70 years'),
            YesNoUnknown::yes(),
            [UnderlyingSuffering::cardioVascular()],
        );

        $this->answersForCase($case)->assertCount(0);
    }

    public function testNoKnownBirthdate(): void
    {
        $case = $this->createCase(
            null, // unknown date of birth
            YesNoUnknown::yes(),
            [UnderlyingSuffering::cardioVascular()],
        );

        $this->answersForCase($case)->assertCount(0);
    }

    public function testNoUnderlyingSufferingWhenDeceased(): void
    {
        $case = $this->createCase(
            $this->faker->dateTimeBetween(startDate: '-80 years', endDate: '-70 years'),
            YesNoUnknown::no(),
            [UnderlyingSuffering::cardioVascular()],
        );

        $this->answersForCase($case)->assertCount(0);
    }

    public function testItHasNoUnderlyingSuffering(): void
    {
        $case = $this->createCase(
            $this->faker->dateTimeBetween(startDate: '-70 years', endDate: '-18 years'),
            YesNoUnknown::yes(),
            [],
        );

        $case->underlying_suffering->hasUnderlyingSuffering = null;

        $this->answersForCase($case)->assertCount(0);
    }

    public function testEmptyItems(): void
    {
        $case = $this->createCase(
            $this->faker->dateTimeBetween(startDate: '-70 years', endDate: '-50 years'),
            YesNoUnknown::yes(),
            [],
        );
        $answers = $this->answersForCase($case)->assertCount(1);
        $answers->assertContainsAnswers([
            new Answer('NCOVondaandcomorV2', '13'),
        ]);
    }

    public function testItemsNull(): void
    {
        $case = $this->createCase(
            $this->faker->dateTimeBetween(startDate: '-70 years', endDate: '-50 years'),
            YesNoUnknown::yes(),
            [],
        );

        $case->underlying_suffering->items = null;

        $answers = $this->answersForCase($case)->assertCount(1);
        $answers->assertContainsAnswers([
            new Answer('NCOVondaandcomorV2', '13'),
        ]);
    }

    public function testSingleItem(): void
    {
        $case = $this->createCase(
            $this->faker->dateTimeBetween(startDate: '-70 years', endDate: '-50 years'),
            YesNoUnknown::yes(),
            [UnderlyingSuffering::cardioVascular()],
        );

        $answers = $this->answersForCase($case);
        $answers->assertContainsAnswer(new Answer('NCOVondaandcomorV2', '3'));
    }

    public function testIsPregnant(): void
    {
        $case = $this->createCase(
            $this->faker->dateTimeBetween(startDate: '-70 years', endDate: '-50 years'),
            YesNoUnknown::yes(),
            [UnderlyingSuffering::hivUntreated()],
        );

        $case->pregnancy->isPregnant = YesNoUnknown::yes();

        $answers = $this->answersForCase($case)->assertCount(2);
        $answers->assertContainsAnswers([
            new Answer('NCOVondaandcomorV2', '1'),
        ]);
    }

    public function testIsOnlyPregnant(): void
    {
        $case = $this->createCase(
            $this->faker->dateTimeBetween(startDate: '-70 years', endDate: '-50 years'),
            YesNoUnknown::yes(),
            [],
        );

        $case->pregnancy->isPregnant = YesNoUnknown::yes();

        $answers = $this->answersForCase($case)->assertCount(1);
        $answers->assertContainsAnswers([
            new Answer('NCOVondaandcomorV2', '1'),
        ]);
    }

    public function testOtherItem(): void
    {
        $case = $this->createCase(
            $this->faker->dateTimeBetween(startDate: '-70 years', endDate: '-50 years'),
            YesNoUnknown::yes(),
            [],
        );

        $case->underlying_suffering->otherItems = [
            'test' => $this->faker->word(),
        ];

        $answers = $this->answersForCase($case);
        $answers->assertContainsAnswer(
            new Answer('NCOVondaandcomorV2', '11'),
        );
    }

    public function testOtherItemAndPregnant(): void
    {
        $case = $this->createCase(
            $this->faker->dateTimeBetween(startDate: '-70 years', endDate: '-50 years'),
            YesNoUnknown::yes(),
            [],
        );

        $case->underlying_suffering->otherItems = [
            'test' => $this->faker->word(),
        ];

        $case->pregnancy->isPregnant = YesNoUnknown::yes();

        $answers = $this->answersForCase($case)->assertCount(2);
        $answers->assertContainsAnswer(
            new Answer('NCOVondaandcomorV2', '11'),
        );
        $answers->assertContainsAnswer(
            new Answer('NCOVondaandcomorV2', '1'),
        );
    }

    public function testMultipleOtherItem(): void
    {
        $case = $this->createCase(
            $this->faker->dateTimeBetween(startDate: '-70 years', endDate: '-50 years'),
            YesNoUnknown::yes(),
            [],
        );

        $case->underlying_suffering->otherItems = [
            'test' => $this->faker->word(),
            'test2' => $this->faker->word(),
        ];

        $answers = $this->answersForCase($case)->assertCount(1);
        $answers->assertContainsAnswer(
            new Answer('NCOVondaandcomorV2', '11'),
        );
    }

    public function testItemIsNull(): void
    {
        $case = $this->createCase(
            $this->faker->dateTimeBetween(startDate: '-70 years', endDate: '-50 years'),
            YesNoUnknown::yes(),
            null,
        );

        $answers = $this->answersForCase($case)->assertCount(1);
        $answers->assertContainsAnswer(
            new Answer('NCOVondaandcomorV2', '13'),
        );
    }

    public function testItemIsNullAndIsPregnant(): void
    {
        $case = $this->createCase(
            $this->faker->dateTimeBetween(startDate: '-70 years', endDate: '-50 years'),
            YesNoUnknown::yes(),
            null,
        );

        $case->pregnancy->isPregnant = YesNoUnknown::yes();

        $answers = $this->answersForCase($case)->assertCount(1);
        $answers->assertContainsAnswer(
            new Answer('NCOVondaandcomorV2', '1'),
        );
    }

    public function testMultipleItems(): void
    {
        $case = $this->createCase(
            $this->faker->dateTimeBetween(startDate: '-70 years', endDate: '-50 years'),
            YesNoUnknown::yes(),
            [
                UnderlyingSuffering::cardioVascular(),
                UnderlyingSuffering::sicklecellDisease(),
                UnderlyingSuffering::diabetes(),
                UnderlyingSuffering::bloodDisease(), // note: bloodDisease has no mapping and so should be excluded
            ],
        );
        $answers = $this->answersForCase($case);
        $answers->assertContainsAnswers([
            new Answer('NCOVondaandcomorV2', '3'),
            new Answer('NCOVondaandcomorV2', '23'),
            new Answer('NCOVondaandcomorV2', '4'),
        ]);
    }

    #[DataProvider('pregnancyDataProvider')]
    public function testBuilderScenarios(
        ?YesNoUnknown $hasUnderlyingSufferingOrMedication,
        ?YesNoUnknown $hasUnderlyingSuffering,
        array $items,
        ?YesNoUnknown $isPregnant,
        array $expectedResults,
    ): void {
        /** @var EloquentCase $case */
        $case = EloquentCase::getSchema()->getVersion(5)->newInstance();
        $case->createdAt = CarbonImmutable::now();
        $case->index->dateOfBirth = $this->faker->dateTimeBetween('-70 years');

        /** @var UnderlyingSufferingV2Up $underlyingSuffering */
        $underlyingSuffering = $case->underlying_suffering;
        $underlyingSuffering->hasUnderlyingSufferingOrMedication = $hasUnderlyingSufferingOrMedication;
        $underlyingSuffering->hasUnderlyingSuffering = $hasUnderlyingSuffering;
        $underlyingSuffering->items = $items;

        /** @var PregnancyV2 $pregnancy */
        $pregnancy = $case->pregnancy;
        $pregnancy->isPregnant = $isPregnant;

        $answers = $this->answersForCase($case);

        if ($expectedResults) {
            $expectedCount = count($expectedResults);
            $exptecedAnswers = [];
            foreach ($expectedResults as $expectedResult) {
                $exptecedAnswers[] = new Answer('NCOVondaandcomorV2', (string) $expectedResult);
            }
            $answers->assertContainsAnswers($exptecedAnswers);
        } else {
            $expectedCount = 0;
        }

        $answers->assertCount($expectedCount);
    }

    public static function pregnancyDataProvider(): array
    {
        return [
            'niets, niets, geen, niets, geen' => [null, null, [], null, []],
            'niets, niets, geen, onbekend, geen' => [null, null, [], YesNoUnknown::unknown(), []],
            'niets, niets, geen, nee, geen' => [null, null, [], YesNoUnknown::no(), []],
            'niets, niets, geen, ja, 1 (zwanger)' => [null, null, [], YesNoUnknown::yes(), [1]],
            'nee, niets, geen, niets, 12 (geen)' => [YesNoUnknown::no(), null, [], null, [12]],
            'nee, niets, geen, onbekend, 12 (geen)' => [YesNoUnknown::no(), null, [], YesNoUnknown::unknown(), [12]],
            'nee, niets, geen, nee, 12 (geen)' => [YesNoUnknown::no(), null, [], YesNoUnknown::no(), [12]],
            'nee, niets, geen, ja, 1 (zwanger)' => [YesNoUnknown::no(), null, [], YesNoUnknown::yes(), [1]],
            'onbekend, niets, geen, niets, 13 (onbekend)' => [YesNoUnknown::unknown(), null, [], null, [13]],
            'onbekend, niets, geen, onbekend, 13 (onbekend)' => [YesNoUnknown::unknown(), null, [], YesNoUnknown::unknown(), [13]],
            'onbekend, niets, geen, nee, 13 (onbekend)' => [YesNoUnknown::unknown(), null, [], YesNoUnknown::no(), [13]],
            'onbekend, niets, geen, ja, 1 (zwanger)' => [YesNoUnknown::unknown(), null, [], YesNoUnknown::yes(), [1]],
            'ja, niets, geen, niets, 13 (onbekend)' => [YesNoUnknown::yes(), null, [], null, [13]],
            'ja, niets, geen, onbekend, 13 (onbekend)' => [YesNoUnknown::yes(), null, [], YesNoUnknown::unknown(), [13]],
            'ja, niets, geen, nee, 13 (onbekend)' => [YesNoUnknown::yes(), null, [], YesNoUnknown::no(), [13]],
            'ja, niets, geen, ja, 1 (zwanger)' => [YesNoUnknown::yes(), null, [], YesNoUnknown::yes(), [1]],
            'ja, nee, geen, niets, 12 (geen)' => [YesNoUnknown::yes(), YesNoUnknown::no(), [], null, [12]],
            'ja, nee, geen, onbekend, 12 (geen)' => [YesNoUnknown::yes(), YesNoUnknown::no(), [], YesNoUnknown::unknown(), [12]],
            'ja, nee, geen, nee, 12 (geen)' => [YesNoUnknown::yes(), YesNoUnknown::no(), [], YesNoUnknown::no(), [12]],
            'ja, nee, geen, ja, 1 (zwanger)' => [YesNoUnknown::yes(), YesNoUnknown::no(), [], YesNoUnknown::yes(), [1]],
            'ja, onbekend, geen, niets, 13 (onbekend)' => [YesNoUnknown::yes(), YesNoUnknown::unknown(), [], null, [13]],
            'ja, onbekend, geen, onbekend, 13 (onbekend)' => [YesNoUnknown::yes(), YesNoUnknown::unknown(), [], YesNoUnknown::unknown(), [13]],
            'ja, onbekend, geen, nee, 13 (onbekend)' => [YesNoUnknown::yes(), YesNoUnknown::unknown(), [], YesNoUnknown::no(), [13]],
            'ja, onbekend, geen, ja, 1 (zwanger)' => [YesNoUnknown::yes(), YesNoUnknown::unknown(), [], YesNoUnknown::yes(), [1]],
            'ja, ja, geen, niets, 13 (onbekend)' => [YesNoUnknown::yes(), YesNoUnknown::yes(), [], null, [13]],
            'ja, ja, geen, onbekend, 13 (onbekend)' => [YesNoUnknown::yes(), YesNoUnknown::yes(), [], YesNoUnknown::unknown(), [13]],
            'ja, ja, geen, nee, 13 (onbekend)' => [YesNoUnknown::yes(), YesNoUnknown::yes(), [], YesNoUnknown::no(), [13]],
            'ja, ja, geen, ja, 1 (zwanger)' => [YesNoUnknown::yes(), YesNoUnknown::yes(), [], YesNoUnknown::yes(), [1]],
            'ja, ja, een aantal, niets, bijbehorende codes' => [YesNoUnknown::yes(), YesNoUnknown::yes(), [UnderlyingSuffering::autoimmuneDisease(), UnderlyingSuffering::diabetesUnstableGlucoselevels()], null, [4, 21]],
            'ja, ja, een aantal, onbekend, bijbehorende codes' => [YesNoUnknown::yes(), YesNoUnknown::yes(), [UnderlyingSuffering::chronic()], YesNoUnknown::unknown(), [9]],
            'ja, ja, een aantal, nee, bijbehorende codes' => [YesNoUnknown::yes(), YesNoUnknown::yes(), [UnderlyingSuffering::cardioVascular(), UnderlyingSuffering::malignity()], YesNoUnknown::no(), [3, 10]],
            'ja, ja, een aantal, ja, bijbehorende codes + 1 (zwanger)' => [YesNoUnknown::yes(), YesNoUnknown::yes(), [UnderlyingSuffering::hivUntreated()], YesNoUnknown::yes(), [1, 19]],
        ];
    }

    /**
     * @param ?array<UnderlyingSuffering> $underlyingSufferingItems
     */
    private function createCase(
        ?DateTimeInterface $dateOfBirth,
        ?YesNoUnknown $hasUnderlyingSuffering,
        ?array $underlyingSufferingItems,
    ): EloquentCase {
        $case = EloquentCase::getSchema()->getVersion(5)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $case->index->dateOfBirth = $dateOfBirth;
        $case->underlying_suffering->hasUnderlyingSuffering = $hasUnderlyingSuffering;
        $case->underlying_suffering->items = $underlyingSufferingItems;
        return $case;
    }
}
