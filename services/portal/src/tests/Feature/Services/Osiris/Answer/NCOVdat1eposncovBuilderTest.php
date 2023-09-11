<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Osiris\Answer;

use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVdat1eposncovBuilder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Generator;
use MinVWS\DBCO\Enum\Models\TestResult;
use MinVWS\DBCO\Enum\Models\TestResultType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVdat1eposncovBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVdat1eposncovBuilderTest extends FeatureTestCase
{
    use AssertAnswers;

    public static function datesProvider(): Generator
    {
        yield 'dateOfResult < selfTestLabTestDate' => [
            CarbonImmutable::make('2022-05-20'),
            CarbonImmutable::make('2022-05-22'),
            CarbonImmutable::make('2022-05-21'),
            '20-05-2022'];
        yield 'testResultdateOfResult < dateOfResult and selfTestLabTestDate' => [
            CarbonImmutable::make('2022-05-21'),
            CarbonImmutable::make('2022-05-22'),
            CarbonImmutable::make('2022-05-20'),
            '20-05-2022'];
        yield 'dateOfResult > selfTestLabTestDate' => [
            CarbonImmutable::make('2022-05-20'),
            CarbonImmutable::make('2022-05-18'),
            CarbonImmutable::make('2022-05-19'),
            '18-05-2022'];
        yield 'only dateOfResult' => [
            CarbonImmutable::make('2022-05-20'),
            null,
            null,
            '20-05-2022',
        ];
        yield 'only selfTestLabTestDate' => [
            null,
            CarbonImmutable::make('2022-05-20'),
            null,
            '20-05-2022',
        ];
        yield 'only testResultDateOfResult' => [
            null,
            null,
            CarbonImmutable::make('2022-05-20'),
            '20-05-2022',
        ];
    }

    #[DataProvider('datesProvider')]
    public function testDates(?DateTimeInterface $dateOfResult, ?DateTimeInterface $selfTestLabTestDate, ?DateTimeInterface $testResultDateOfResult, string $expectedValue): void
    {
        $case = $this->createCovidCase($dateOfResult, $selfTestLabTestDate, $testResultDateOfResult);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVdat1eposncov', $expectedValue));
    }

    public function testDatesNull(): void
    {
        $case = $this->createCovidCase(null, null, null);
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCovidCase(?DateTimeInterface $dateOfResult, ?DateTimeInterface $selfTestLabTestDate, ?DateTimeInterface $testResultDateOfResult): EloquentCase
    {
        $case = $this->createCase([
            'test' => Test::newInstanceWithVersion(2, static function (Test $test) use ($dateOfResult, $selfTestLabTestDate): void {
                $test->dateOfResult = $dateOfResult;
                $test->selfTestLabTestDate = $selfTestLabTestDate;
            }),
        ]);

        if ($testResultDateOfResult !== null) {
            $case->testResults()->save(
                $this->createTestResult([
                    'type' => TestResultType::lab(),
                    'result' => TestResult::positive(),
                    'date_of_result' => $testResultDateOfResult,
                ]),
            );
        }

        $case->save();

        return $case;
    }
}
