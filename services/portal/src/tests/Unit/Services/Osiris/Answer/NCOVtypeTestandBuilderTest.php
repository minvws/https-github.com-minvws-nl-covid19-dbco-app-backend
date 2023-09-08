<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVtypeTestandBuilder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\LabTestIndicator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVtypeTestandBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVtypeTestandBuilderTest extends TestCase
{
    use AssertAnswers;

    #[DataProvider('indicatorsDataProvider')]
    public function testIndicators(
        int $caseVersion,
        ?InfectionIndicator $infectionIndicator,
        ?LabTestIndicator $labTestIndicator,
        ?string $otherLabTestIndicator,
        string $expectedResult,
    ): void {
        $case = $this->createCase($caseVersion, $infectionIndicator, $labTestIndicator, $otherLabTestIndicator);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVtypeTestand', $expectedResult));
    }

    public static function indicatorsDataProvider(): array
    {
        return [
            '3, labtest, other, test' => [3, InfectionIndicator::labTest(), LabTestIndicator::other(), 'test', 'test'],
            '3, labtest, other, a\b' => [3, InfectionIndicator::labTest(), LabTestIndicator::other(), "a\nb", "a\nb"],
            '3, labtest, other, empty' => [3, InfectionIndicator::labTest(), LabTestIndicator::other(), '', ''],

            '4, labtest, other, test' => [4, InfectionIndicator::labTest(), LabTestIndicator::other(), 'test', 'test'],
            '4, labtest, other, a\b' => [4, InfectionIndicator::labTest(), LabTestIndicator::other(), "a\nb", "a\nb"],
            '4, labtest, other, empty' => [4, InfectionIndicator::labTest(), LabTestIndicator::other(), '', ''],
        ];
    }

    #[DataProvider('indicatorsForEmptyResultsDataProvider')]
    public function testIndicatorsForEmptyResults(
        int $caseVersion,
        ?InfectionIndicator $infectionIndicator,
        ?LabTestIndicator $labTestIndicator,
        ?string $otherLabTestIndicator,
    ): void {
        $case = $this->createCase($caseVersion, $infectionIndicator, $labTestIndicator, $otherLabTestIndicator);
        $this->answersForCase($case)->assertEmpty();
    }

    public static function indicatorsForEmptyResultsDataProvider(): array
    {
        return [
            '3, labtest, molecular, test' => [3, InfectionIndicator::labTest(), LabTestIndicator::molecular(), 'test'],
            '3, labtest, other, null' => [3, InfectionIndicator::labTest(), LabTestIndicator::other(), null],
            '3, labtest, null, test' => [3, InfectionIndicator::labTest(), null, 'test'],
            '3, selftest, null, test' => [3, InfectionIndicator::selfTest(), LabTestIndicator::other(), 'test'],
            '3, null, other, test' => [3, null, LabTestIndicator::other(), 'test'],

            '4, labtest, molecular, test' => [4, InfectionIndicator::labTest(), LabTestIndicator::molecular(), 'test'],
            '4, labtest, other, null' => [4, InfectionIndicator::labTest(), LabTestIndicator::other(), null],
            '4, labtest, null, test' => [4, InfectionIndicator::labTest(), null, 'test'],
            '4, selftest, null, test' => [4, InfectionIndicator::selfTest(), LabTestIndicator::other(), 'test'],
            '4, null, other, test' => [4, null, LabTestIndicator::other(), 'test'],
        ];
    }

    private function createCase(
        int $caseVersion,
        ?InfectionIndicator $infectionIndicator,
        ?LabTestIndicator $labTestIndicator,
        ?string $otherLabTestIndicator,
    ): EloquentCase {
        /** @var EloquentCase $case */
        $case = EloquentCase::getSchema()->getVersion($caseVersion)->newInstance();
        $case->createdAt = CarbonImmutable::now();

        $case->test->infectionIndicator = $infectionIndicator;
        $case->test->labTestIndicator = $labTestIndicator;
        $case->test->otherLabTestIndicator = $otherLabTestIndicator;

        return $case;
    }
}
