<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVtypeTestBuilder;
use Carbon\CarbonImmutable;
use Generator;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\LabTestIndicator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVtypeTestBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVtypeTestBuilderTest extends TestCase
{
    use AssertAnswers;

    #[DataProvider('indicatorDataProvider')]
    public function testIndicator(
        int $caseVersion,
        ?InfectionIndicator $infectionIndicator,
        ?LabTestIndicator $labTestIndicator,
        ?string $otherLabTestIndicator,
        string $expectedValue,
    ): void {
        $case = $this->createCase($caseVersion, $infectionIndicator, $labTestIndicator, $otherLabTestIndicator);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVtypeTest', $expectedValue));
    }

    public static function indicatorDataProvider(): Generator
    {
        yield '3, all null' => [3, null, null, null, '4'];
        yield '3, null, molecular' => [3, null, LabTestIndicator::molecular(), null, '4'];
        yield '3, labtest, molecular' => [3, InfectionIndicator::labTest(), LabTestIndicator::molecular(), null, '1'];
        yield '3, labtest, antigen' => [3, InfectionIndicator::labTest(), LabTestIndicator::antigen(), null, '2'];
        yield '3, labtest, other, test' => [3, InfectionIndicator::labTest(), LabTestIndicator::other(), 'test', '3'];
        yield '3, labtest, other, null' => [3, InfectionIndicator::labTest(), LabTestIndicator::other(), null, '3'];
        yield '3, labtest, null' => [3, InfectionIndicator::labTest(), null, null, '4'];
        yield '3, self-test, null' => [3, InfectionIndicator::selfTest(), null, null, '5'];
        yield '3, self-test, molecular' => [3, InfectionIndicator::selfTest(), LabTestIndicator::molecular(), null, '5'];
        yield '3, unknown, null' => [3, InfectionIndicator::unknown(), null, null, '4'];
        yield '3, unknown, molecular' => [3, InfectionIndicator::unknown(), LabTestIndicator::molecular(), null, '4'];

        yield '4, all null' => [4, null, null, null, '4'];
        yield '4, null, molecular' => [4, null, LabTestIndicator::molecular(), null, '4'];
        yield '4, labtest, molecular' => [4, InfectionIndicator::labTest(), LabTestIndicator::molecular(), null, '1'];
        yield '4, labtest, antigen' => [4, InfectionIndicator::labTest(), LabTestIndicator::antigen(), null, '2'];
        yield '4, labtest, other, test' => [4, InfectionIndicator::labTest(), LabTestIndicator::other(), 'test', '3'];
        yield '4, labtest, other, null' => [4, InfectionIndicator::labTest(), LabTestIndicator::other(), null, '3'];
        yield '4, labtest, null' => [4, InfectionIndicator::labTest(), null, null, '4'];
        yield '4, self-test, null' => [4, InfectionIndicator::selfTest(), null, null, '5'];
        yield '4, self-test, molecular' => [4, InfectionIndicator::selfTest(), LabTestIndicator::molecular(), null, '5'];
        yield '4, unknown, null' => [4, InfectionIndicator::unknown(), null, null, '4'];
        yield '4, unknown, molecular' => [4, InfectionIndicator::unknown(), LabTestIndicator::molecular(), null, '4'];
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
