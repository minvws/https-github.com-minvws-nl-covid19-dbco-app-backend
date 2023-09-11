<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVDtHerTestBuilder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\SelfTestIndicator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVDtHerTestBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVDtHerTestBuilderTest extends TestCase
{
    use AssertAnswers;

    #[DataProvider('selfTestIndicatorDataProvider')]
    public function testDateOfTest(
        int $caseVersion,
        ?InfectionIndicator $infectionIndicator,
        SelfTestIndicator $selfTestIndicator,
        string $date,
        string $expectedResult,
    ): void {
        $selfTestLabTestDate = CarbonImmutable::createFromFormat('d-m-Y', $date);
        $case = $this->createCase($caseVersion, $infectionIndicator, $selfTestIndicator, $selfTestLabTestDate);

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVDtHerTest', $expectedResult));
    }

    public static function selfTestIndicatorDataProvider(): array
    {
        return [
            '3, selftest, molecular, 1-2-2000' => [3, InfectionIndicator::selfTest(), SelfTestIndicator::molecular(), '1-1-2000', '01-01-2000'],
            '3, unknown, antigen, 25-11-2010' => [3, InfectionIndicator::unknown(), SelfTestIndicator::antigen(), '25-11-2010', '25-11-2010'],

            '4, selftest, molecular, 1-2-2000' => [4, InfectionIndicator::selfTest(), SelfTestIndicator::molecular(), '1-1-2000', '01-01-2000'],
            '4, unknown, antigen, 25-11-2010' => [4, InfectionIndicator::unknown(), SelfTestIndicator::antigen(), '25-11-2010', '25-11-2010'],
        ];
    }

    #[DataProvider('herTestEmptyDataProvider')]
    public function testEmpty(
        int $caseVersion,
        ?InfectionIndicator $infectionIndicator,
        ?SelfTestIndicator $selfTestIndicator,
        ?string $date,
    ): void {
        $selfTestLabTestDate = $date ? CarbonImmutable::createFromFormat('d-m-Y', $date) : null;
        $case = $this->createCase($caseVersion, $infectionIndicator, $selfTestIndicator, $selfTestLabTestDate);

        $this->answersForCase($case)->assertEmpty();
    }

    public static function herTestEmptyDataProvider(): array
    {
        return [
            '3, labtest, molecular, 1-2-2000' => [3, InfectionIndicator::labTest(), SelfTestIndicator::molecular(), '1-1-2000'],
            '3, unknown, 1-2-2000' => [3, InfectionIndicator::labTest(), SelfTestIndicator::unknown(), '1-1-2000'],
            '3, molecular, null' => [3, InfectionIndicator::labTest(), SelfTestIndicator::molecular(), null],
            '3, null, null' => [3, null, null, null],

            '4, labtest, molecular, 1-2-2000' => [4, InfectionIndicator::labTest(), SelfTestIndicator::molecular(), '1-1-2000'],
            '4, unknown, 1-2-2000' => [4, InfectionIndicator::labTest(), SelfTestIndicator::unknown(), '1-1-2000'],
            '4, molecular, null' => [4, InfectionIndicator::labTest(), SelfTestIndicator::molecular(), null],
            '4, null, null' => [4, null, null, null],
        ];
    }

    private function createCase(
        int $caseVersion,
        ?InfectionIndicator $infectionIndicator,
        ?SelfTestIndicator $selfTestIndicator,
        ?DateTimeInterface $selfTestLabTestDate,
    ): EloquentCase {
        /** @var EloquentCase $case */
        $case = EloquentCase::getSchema()->getVersion($caseVersion)->newInstance();
        $case->createdAt = CarbonImmutable::now();

        $case->test->infectionIndicator = $infectionIndicator;
        $case->test->selfTestIndicator = $selfTestIndicator;
        $case->test->selfTestLabTestDate = $selfTestLabTestDate;

        return $case;
    }
}
