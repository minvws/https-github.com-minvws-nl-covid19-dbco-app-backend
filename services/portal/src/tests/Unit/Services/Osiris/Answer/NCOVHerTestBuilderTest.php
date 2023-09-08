<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVHerTestBuilder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\SelfTestIndicator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVHerTestBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVHerTestBuilderTest extends TestCase
{
    use AssertAnswers;

    #[DataProvider('selfTestsProvider')]
    public function testItAddsTheNcovhertestXmlNode(
        int $caseVersion,
        ?SelfTestIndicator $selfTestIndicator,
        ?string $selfTestLabTestDate,
        string $code,
    ): void {
        $case = $this->createCase(
            $caseVersion,
            InfectionIndicator::selfTest(),
            $selfTestIndicator,
            $selfTestLabTestDate ? CarbonImmutable::createFromFormat('d-m-Y', $selfTestLabTestDate) : null,
        );
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVHerTest', $code));
    }

    public static function selfTestsProvider(): array
    {
        return [
            '3, molecular, 1-1-2000' => [3, SelfTestIndicator::molecular(), '1-1-2000', '1'],
            '3, molecular, null' => [3, SelfTestIndicator::molecular(), null, '1'],
            '3, antigen, 1-1-2000' => [3, SelfTestIndicator::antigen(), '1-1-2000', '2'],
            '3, antigen, null' => [3, SelfTestIndicator::antigen(), null, '2'],
            '3, planned retest, 1-1-2000' => [3, SelfTestIndicator::plannedRetest(), '1-1-2000', '3'],
            '3, planned retest, null' => [3, SelfTestIndicator::plannedRetest(), null, '3'],
            '3, noretest, 1-1-2000' => [3, SelfTestIndicator::noRetest(), '1-1-2000', '4'],
            '3, planned unknown, 1-1-2000' => [3, SelfTestIndicator::unknown(), '1-1-2000', '5'],

            '4, molecular, 1-1-2000' => [4, SelfTestIndicator::molecular(), '1-1-2000', '1'],
            '4, molecular, null' => [4, SelfTestIndicator::molecular(), null, '1'],
            '4, antigen, 1-1-2000' => [4, SelfTestIndicator::antigen(), '1-1-2000', '2'],
            '4, antigen, null' => [4, SelfTestIndicator::antigen(), null, '2'],
            '4, planned retest, 1-1-2000' => [4, SelfTestIndicator::plannedRetest(), '1-1-2000', '3'],
            '4, planned retest, null' => [4, SelfTestIndicator::plannedRetest(), null, '3'],
            '4, noretest, 1-1-2000' => [4, SelfTestIndicator::noRetest(), '1-1-2000', '4'],
            '4, planned unknown, 1-1-2000' => [4, SelfTestIndicator::unknown(), '1-1-2000', '5'],
        ];
    }

    #[DataProvider('otherTestsProvider')]
    public function testItDoesNotAddTheNcovhertestXmlNode(
        int $caseVersion,
        InfectionIndicator $infectionIndicator,
        ?SelfTestIndicator $selfTestIndicator,
        ?string $selfTestLabTestDate,
    ): void {
        $case = $this->createCase(
            $caseVersion,
            $infectionIndicator,
            $selfTestIndicator,
            $selfTestLabTestDate ? CarbonImmutable::createFromFormat('d-m-Y', $selfTestLabTestDate) : null,
        );
        $this->answersForCase($case)->assertEmpty();
    }

    public static function otherTestsProvider(): array
    {
        return [
            '3, labtest, molecular' => [3, InfectionIndicator::labTest(), SelfTestIndicator::molecular(), null],
            '3, labtest, molecular, 1-1-2000' => [3, InfectionIndicator::labTest(), SelfTestIndicator::molecular(), '1-1-2000'],
            '3, labtest, antigen' => [3, InfectionIndicator::labTest(), SelfTestIndicator::antigen(), null],
            '3, labtest, planned retest' => [3, InfectionIndicator::labTest(), SelfTestIndicator::plannedRetest(), null],
            '3, labtest, null' => [3, InfectionIndicator::labTest(), null, null],

            '3, unknown, molecular' => [3, InfectionIndicator::unknown(), SelfTestIndicator::molecular(), null],
            '3, unknown, antigen' => [3, InfectionIndicator::unknown(), SelfTestIndicator::antigen(), null],

            '4, labtest, molecular' => [4, InfectionIndicator::labTest(), SelfTestIndicator::molecular(), null],
            '4, labtest, antigen' => [4, InfectionIndicator::labTest(), SelfTestIndicator::antigen(), null],
            '4, labtest, planned retest' => [4, InfectionIndicator::labTest(), SelfTestIndicator::plannedRetest(), null],
            '4, labtest, null' => [4, InfectionIndicator::labTest(), null, null],

            '4, unknown, molecular' => [4, InfectionIndicator::unknown(), SelfTestIndicator::molecular(), null],
            '4, unknown, antigen' => [4, InfectionIndicator::unknown(), SelfTestIndicator::antigen(), null],
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

        $case->test->selfTestIndicator = $selfTestIndicator;
        $case->test->infectionIndicator = $infectionIndicator;
        $case->test->selfTestLabTestDate = $selfTestLabTestDate;

        return $case;
    }
}
