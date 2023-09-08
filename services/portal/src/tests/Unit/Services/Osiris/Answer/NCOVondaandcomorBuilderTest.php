<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVondaandcomorBuilder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Generator;
use MinVWS\DBCO\Enum\Models\UnderlyingSuffering;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;
use function count;
use function sprintf;

#[Builder(NCOVondaandcomorBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVondaandcomorBuilderTest extends TestCase
{
    use AssertAnswers;

    public static function emptyResultProvider(): Generator
    {
        yield 'alive' => [
            YesNoUnknown::no(),
            null,
            CarbonImmutable::make("50 years ago"),
            YesNoUnknown::yes(),
            [UnderlyingSuffering::cardioVascular()],
            [],
        ];

        yield 'deceased, but no deceased date' => [
            YesNoUnknown::yes(),
            null,
            CarbonImmutable::make("50 years ago"),
            YesNoUnknown::yes(),
            [UnderlyingSuffering::cardioVascular()],
            [],
        ];

        yield 'over 70 years' => [
            YesNoUnknown::yes(),
            CarbonImmutable::yesterday(),
            CarbonImmutable::make("71 years ago"),
            YesNoUnknown::yes(),
            [UnderlyingSuffering::cardioVascular()],
            [],
        ];

        yield 'no known birthdate' => [
            YesNoUnknown::yes(),
            CarbonImmutable::yesterday(),
            null,
            YesNoUnknown::yes(),
            [UnderlyingSuffering::cardioVascular()],
            [],
        ];

        yield 'no underlying suffering' => [
            YesNoUnknown::yes(),
            CarbonImmutable::yesterday(),
            CarbonImmutable::make("50 years ago"),
            YesNoUnknown::no(),
            [UnderlyingSuffering::cardioVascular()],
            [],
        ];

        yield 'empty items' => [
            YesNoUnknown::yes(),
            CarbonImmutable::yesterday(),
            CarbonImmutable::make("50 years ago"),
            YesNoUnknown::yes(),
            [],
            [],
        ];
    }

    public static function validResultProvider(): Generator
    {
        yield 'single item' => [
            YesNoUnknown::yes(),
            CarbonImmutable::yesterday(),
            CarbonImmutable::make("50 years ago"),
            YesNoUnknown::yes(),
            [UnderlyingSuffering::cardioVascular()],
            ['3'],
        ];

        yield 'multiple items' => [
            YesNoUnknown::yes(),
            CarbonImmutable::yesterday(),
            CarbonImmutable::make("50 years ago"),
            YesNoUnknown::yes(),
            [UnderlyingSuffering::cardioVascular(), UnderlyingSuffering::bloodDisease(), UnderlyingSuffering::diabetes()],
            ['3', '11', '4'],
        ];
    }

    /**
     * @param ?array<UnderlyingSuffering> $underlyingSufferingItems
     * @param array<string> $expectedValues
     */
    #[DataProvider('emptyResultProvider')]
    #[DataProvider('validResultProvider')]
    public function testItems(
        YesNoUnknown $isDeceased,
        ?DateTimeInterface $deceasedAt,
        ?DateTimeInterface $dateOfBirth,
        ?YesNoUnknown $hasUnderlyingSuffering,
        ?array $underlyingSufferingItems,
        array $expectedValues,
    ): void {
        $case = $this->createCase($isDeceased, $deceasedAt, $dateOfBirth, $hasUnderlyingSuffering, $underlyingSufferingItems);
        $answers = $this->answersForCase($case);
        $answers->assertCount(count($expectedValues));
        foreach ($expectedValues as $expectedValue) {
            $answers->assertContainsAnswer(new Answer('NCOVondaandcomor', $expectedValue));
        }
    }

    public function testUnderlyingSufferingMappings(): void
    {
        $values = [];
        $underlyingSufferingItems = UnderlyingSuffering::getVersion(1)->all();

        foreach ($underlyingSufferingItems as $underlyingSufferingItem) {
            $case = $this->createCase(
                YesNoUnknown::yes(),
                CarbonImmutable::yesterday(),
                CarbonImmutable::make('50 years ago'),
                YesNoUnknown::yes(),
                [$underlyingSufferingItem],
            );

            $answers = $this->answersForCase($case);
            $answers->assertCount(1, sprintf('no mapping found for %s', $underlyingSufferingItem->value));
            $this->assertEquals('NCOVondaandcomor', $answers[0]->code);
            $this->assertIsNumeric($answers[0]->value);
            $values[] = $answers[0]->value;
        }

        $this->assertEquals(count(UnderlyingSuffering::getVersion(1)->all()), count($values));
    }

    /**
     * @param ?array<UnderlyingSuffering> $underlyingSufferingItems
     */
    private function createCase(
        YesNoUnknown $isDeceased,
        ?DateTimeInterface $deceasedAt,
        ?DateTimeInterface $dateOfBirth,
        ?YesNoUnknown $hasUnderlyingSuffering,
        ?array $underlyingSufferingItems,
    ): EloquentCase {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = $this->faker->dateTimeBetween(
            startDate: '2022-01-01 00:00:00',
            endDate: '2022-06-01 00:00:00',
        ); //Within Osiris V9 range
        $case->deceased->isDeceased = $isDeceased;
        $case->deceased->deceasedAt = $deceasedAt;
        $case->index->dateOfBirth = $dateOfBirth;
        $case->underlying_suffering->hasUnderlyingSuffering = $hasUnderlyingSuffering;
        $case->underlying_suffering->items = $underlyingSufferingItems;
        return $case;
    }
}
