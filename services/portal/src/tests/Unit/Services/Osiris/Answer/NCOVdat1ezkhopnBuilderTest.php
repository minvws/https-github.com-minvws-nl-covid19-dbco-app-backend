<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVdat1ezkhopnBuilder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVdat1ezkhopnBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVdat1ezkhopnBuilderTest extends TestCase
{
    use AssertAnswers;

    #[DataProvider('notEmptyDataProvider')]
    public function testAdmittedAt(
        int $caseVersion,
        ?YesNoUnknown $isAdmitted,
        string $admittedAt,
        string $expectedResult,
    ): void {
        $case = $this->createCase($caseVersion, $isAdmitted, CarbonImmutable::createFromFormat('d-m-Y', $admittedAt));
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVdat1ezkhopn', $expectedResult));
    }

    public static function notEmptyDataProvider(): array
    {
        return [
            '3, yes, 10-5-2022' => [3, YesNoUnknown::yes(), '10-5-2022', '10-05-2022'],

            '4, yes, 10-5-2022' => [4, YesNoUnknown::yes(), '10-5-2022', '10-05-2022'],
        ];
    }

    #[DataProvider('emptyDataProvider')]
    public function testEmptyAnswer(
        int $caseVersion,
        ?YesNoUnknown $isAdmitted,
        ?string $admittedAt,
    ): void {
        $case = $this->createCase(
            $caseVersion,
            $isAdmitted,
            $admittedAt ? CarbonImmutable::createFromFormat('d-m-Y', $admittedAt) : null,
        );
        $this->answersForCase($case)->assertEmpty();
    }

    public static function emptyDataProvider(): array
    {
        return [
            '3, yes, null' => [3, YesNoUnknown::yes(), null],
            '3, no, 10-5-2022' => [3, YesNoUnknown::no(), '10-05-2022'],
            '3, unknown, 10-5-2022' => [3, YesNoUnknown::unknown(), '10-05-2022'],
            '3, null, 10-5-2022' => [3, null, '10-05-2022'],

            '4, yes, null' => [4, YesNoUnknown::yes(), null],
            '4, no, 10-5-2022' => [4, YesNoUnknown::no(), '10-05-2022'],
            '4, unknown, 10-5-2022' => [4, YesNoUnknown::unknown(), '10-05-2022'],
            '4, null, 10-5-2022' => [4, null, '10-05-2022'],
        ];
    }

    private function createCase(
        int $caseVersion,
        ?YesNoUnknown $isAdmitted,
        ?DateTimeInterface $admittedAt,
    ): EloquentCase {
        /** @var EloquentCase $case */
        $case = EloquentCase::getSchema()->getVersion($caseVersion)->newInstance();
        $case->createdAt = CarbonImmutable::now();

        $case->hospital->isAdmitted = $isAdmitted;
        $case->hospital->admittedAt = $admittedAt;

        return $case;
    }
}
