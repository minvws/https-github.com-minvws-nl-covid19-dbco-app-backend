<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVpatZhsIndBuilder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\HospitalReason;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVpatZhsIndBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVpatZhsIndBuilderTest extends TestCase
{
    use AssertAnswers;

    #[DataProvider('notEmptyDataProvider')]
    public function testReasonCovid(
        int $caseVersion,
        ?YesNoUnknown $isAdmitted,
        ?HospitalReason $reason,
        ?string $admittedAt,
        string $expectedResult,
    ): void {
        $case = $this->createCase(
            $caseVersion,
            $isAdmitted,
            $reason,
            $admittedAt ? CarbonImmutable::createFromFormat('d-m-Y', $admittedAt) : null,
        );
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVpatZhsInd', $expectedResult));
    }

    public static function notEmptyDataProvider(): array
    {
        return [
            '3, yes, covid, 1-1-2000' => [3, YesNoUnknown::yes(), HospitalReason::covid(), '1-1-2000', '1'],
            '3, yes, covid, null' => [3, YesNoUnknown::yes(), HospitalReason::covid(), null, '1'],
            '3, yes, other, 1-1-2000' => [3, YesNoUnknown::yes(), HospitalReason::other(), '1-1-2000', '2'],
            '3, yes, null, 1-1-2000' => [3, YesNoUnknown::yes(), null, '1-1-2000', '3'],

            '4, yes, covid, 1-1-2000' => [4, YesNoUnknown::yes(), HospitalReason::covid(), '1-1-2000', '1'],
            '4, yes, covid, null' => [4, YesNoUnknown::yes(), HospitalReason::covid(), null, '1'],
            '4, yes, other, 1-1-2000' => [4, YesNoUnknown::yes(), HospitalReason::other(), '1-1-2000', '2'],
            '4, yes, null, 1-1-2000' => [4, YesNoUnknown::yes(), null, '1-1-2000', '3'],
        ];
    }

    #[DataProvider('emptyDataProvider')]
    public function testNotAdmitted(
        int $caseVersion,
        ?YesNoUnknown $isAdmitted,
        ?HospitalReason $reason,
    ): void {
        $case = $this->createCase($caseVersion, $isAdmitted, $reason, null);
        $this->answersForCase($case)->assertEmpty();
    }

    public static function emptyDataProvider(): array
    {
        return [
            '3, no, covid' => [3, YesNoUnknown::no(), HospitalReason::covid()],
            '3, unknown, covid' => [3, YesNoUnknown::unknown(), HospitalReason::covid()],
            '3, null, covid' => [3, null, HospitalReason::covid()],

            '4, no, covid' => [4, YesNoUnknown::no(), HospitalReason::covid()],
            '4, unknown, covid' => [4, YesNoUnknown::unknown(), HospitalReason::covid()],
            '4, null, covid' => [4, null, HospitalReason::covid()],
        ];
    }

    private function createCase(
        int $caseVersion,
        ?YesNoUnknown $isAdmitted,
        ?HospitalReason $reason,
        ?DateTimeInterface $admittedAt,
    ): EloquentCase {
        /** @var EloquentCase $case */
        $case = EloquentCase::getSchema()->getVersion($caseVersion)->newInstance();
        $case->createdAt = CarbonImmutable::now();

        $case->hospital->isAdmitted = $isAdmitted;
        $case->hospital->reason = $reason;
        $case->hospital->admittedAt = $admittedAt;

        return $case;
    }
}
