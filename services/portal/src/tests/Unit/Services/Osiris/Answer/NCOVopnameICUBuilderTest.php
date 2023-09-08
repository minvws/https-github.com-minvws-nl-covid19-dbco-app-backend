<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVopnameICUBuilder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVopnameICUBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVopnameICUBuilderTest extends TestCase
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
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVopnameICU', $expectedResult));
    }

    public static function notEmptyDataProvider(): array
    {
        return [
            '3, 1-1-2000' => [3, '1-1-2000', 'J'],
            '3, null' => [3, null, 'Onb'],

            '4, 1-1-2000' => [4, '1-1-2000', 'J'],
            '4, yes, null' => [4, null, 'Onb'],
        ];
    }

    #[DataProvider('emptyDataProvider')]
    public function testEmpty(
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
            '3, no, yes, null' => [3, YesNoUnknown::no(), YesNoUnknown::yes(), null],
            '3, unknown, yes, null' => [3, YesNoUnknown::unknown(), YesNoUnknown::yes(), null],
            '3, null, yes, null' => [3, null, YesNoUnknown::yes(), null],
            '3, yes, no, 1-1-2000' => [3, YesNoUnknown::yes(), YesNoUnknown::no(), '1-1-2000'],
            '3, yes, unkown, 1-1-2000' => [3, YesNoUnknown::yes(), YesNoUnknown::unknown(), '1-1-2000'],
            '3, yes, null, 1-1-2000' => [3, YesNoUnknown::yes(), null, '1-1-2000'],
            '3, null, null, 1-1-2000' => [3, null, null, '1-1-2000'],

            '4, no, yes, null' => [4, YesNoUnknown::no(), YesNoUnknown::yes(), null],
            '4, unknown, yes, null' => [4, YesNoUnknown::unknown(), YesNoUnknown::yes(), null],
            '4, null, yes, null' => [4, null, YesNoUnknown::yes(), null],
            '4, yes, no, 1-1-2000' => [4, YesNoUnknown::yes(), YesNoUnknown::no(), '1-1-2000'],
            '4, yes, unknown, 1-1-2000' => [4, YesNoUnknown::yes(), YesNoUnknown::unknown(), '1-1-2000'],
            '4, yes, null, 1-1-2000' => [4, YesNoUnknown::yes(), null, '1-1-2000'],
            '4, null, null, 1-1-2000' => [4, null, null, '1-1-2000'],
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
