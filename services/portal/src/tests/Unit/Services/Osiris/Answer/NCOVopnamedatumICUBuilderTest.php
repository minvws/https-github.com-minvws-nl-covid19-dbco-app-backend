<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVopnamedatumICUBuilder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVopnamedatumICUBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVopnamedatumICUBuilderTest extends TestCase
{
    use AssertAnswers;

    #[DataProvider('notEmptyDataProvider')]
    public function testNotEmpty(
        int $caseVersion,
        ?string $admittedInICUAt,
        string $expectedResult,
    ): void {
        $case = $this->createCase(
            $caseVersion,
            YesNoUnknown::yes(),
            YesNoUnknown::yes(),
            $admittedInICUAt ? CarbonImmutable::createFromFormat('d-m-Y', $admittedInICUAt) : null,
        );
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVopnamedatumICU', $expectedResult));
    }

    public static function notEmptyDataProvider(): array
    {
        return [
            '3, yes, yes, 1-1-2000' => [3, '1-1-2000', '01-01-2000'],

            '4, yes, yes, 1-1-2000' => [4, '1-1-2000', '01-01-2000'],
        ];
    }

    #[DataProvider('emptyDataProvider')]
    public function testNoDate(
        int $caseVersion,
        ?YesNoUnknown $isAdmitted,
        ?YesNoUnknown $isInICU,
        ?string $admittedInICUAt,
    ): void {
        $case = $this->createCase(
            $caseVersion,
            $isAdmitted,
            $isInICU,
            $admittedInICUAt ? CarbonImmutable::createFromFormat('d-m-Y', $admittedInICUAt) : null,
        );
        $this->answersForCase($case)->assertEmpty();
    }

    public static function emptyDataProvider(): array
    {
        return [
            '3, yes, no, null' => [3, YesNoUnknown::yes(), YesNoUnknown::no(), null],
            '3, no, yes, null' => [3, YesNoUnknown::no(), YesNoUnknown::yes(), null],
            '3, no, no, null' => [3, YesNoUnknown::no(), YesNoUnknown::no(), null],
            '3, yes, yes, null' => [3, YesNoUnknown::yes(), YesNoUnknown::yes(), null],

            '4, yes, no, null' => [4, YesNoUnknown::yes(), YesNoUnknown::no(), null],
            '4, no, yes, null' => [4, YesNoUnknown::no(), YesNoUnknown::yes(), null],
            '4, no, no, null' => [4, YesNoUnknown::no(), YesNoUnknown::no(), null],
            '4, yes, yes, null' => [4, YesNoUnknown::yes(), YesNoUnknown::yes(), null],
        ];
    }

    private function createCase(
        int $caseVersion,
        ?YesNoUnknown $isAdmitted,
        ?YesNoUnknown $isInICU,
        ?DateTimeInterface $admittedInICUAt,
    ): EloquentCase {
        /** @var EloquentCase $case */
        $case = EloquentCase::getSchema()->getVersion($caseVersion)->newInstance();
        $case->createdAt = CarbonImmutable::now();

        $case->hospital->isAdmitted = $isAdmitted;
        $case->hospital->isInICU = $isInICU;
        $case->hospital->admittedInICUAt = $admittedInICUAt;

        return $case;
    }
}
