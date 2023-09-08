<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVondaandoverigBuilder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Generator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;
use function implode;

#[Builder(NCOVondaandoverigBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVondaandoverigBuilderTest extends TestCase
{
    use AssertAnswers;

    public static function emptyResultProvider(): Generator
    {
        yield 'alive' => [
            CarbonImmutable::make("50 years ago"),
            YesNoUnknown::yes(),
            ['a'],
            ['a'],
        ];

        yield 'over 70 years' => [
            CarbonImmutable::make("71 years ago"),
            YesNoUnknown::yes(),
            ['a'],
            [],
        ];

        yield 'no known birthdate' => [
            null,
            YesNoUnknown::yes(),
            ['a'],
            [],
        ];

        yield 'no underlying suffering' => [
            CarbonImmutable::make("50 years ago"),
            YesNoUnknown::no(),
            ['a'],
            [],
        ];

        yield 'empty other items' => [
            CarbonImmutable::make("50 years ago"),
            YesNoUnknown::yes(),
            [],
            [],
        ];
    }

    public static function validResultProvider(): Generator
    {
        yield 'single item' => [
            CarbonImmutable::make("50 years ago"),
            YesNoUnknown::yes(),
            ['a'],
            ['a'],
        ];

        yield 'single item, multi-line' => [
            CarbonImmutable::make("50 years ago"),
            YesNoUnknown::yes(),
            ["a\nb"],
            ["a\nb"],
        ];

        yield 'multiple items' => [
            CarbonImmutable::make("50 years ago"),
            YesNoUnknown::yes(),
            ['a', 'b', "c\nd"],
            ['a', 'b', "c\nd"],
        ];
    }

    /**
     * @param ?array<string> $otherUnderlyingSufferingItems
     * @param array<string> $expectedValues
     */
    #[DataProvider('emptyResultProvider')]
    #[DataProvider('validResultProvider')]
    public function testItems(
        ?DateTimeInterface $dateOfBirth,
        ?YesNoUnknown $hasUnderlyingSuffering,
        ?array $otherUnderlyingSufferingItems,
        array $expectedValues,
    ): void {
        $case = $this->createCase($dateOfBirth, $hasUnderlyingSuffering, $otherUnderlyingSufferingItems);
        $answers = $this->answersForCase($case);
        $answers->assertContainsAnswer(new Answer('NCOVondaandoverig', implode(',', $expectedValues)));
    }

    /**
     * @param ?array<string> $otherUnderlyingSufferingItems
     */
    private function createCase(
        ?DateTimeInterface $dateOfBirth,
        ?YesNoUnknown $hasUnderlyingSuffering,
        ?array $otherUnderlyingSufferingItems,
    ): EloquentCase {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $case->index->dateOfBirth = $dateOfBirth;
        $case->underlying_suffering->hasUnderlyingSuffering = $hasUnderlyingSuffering;
        $case->underlying_suffering->otherItems = $otherUnderlyingSufferingItems;
        return $case;
    }
}
