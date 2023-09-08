<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\TestResult;
use App\Repositories\TestResultRepository;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVmonnrBuilder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use MinVWS\DBCO\Enum\Models\TestResultSource;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVmonnrBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVmonnrBuilderTest extends TestCase
{
    use AssertAnswers;

    #[DataProvider('monsterNumberDataProvider')]
    public function testMonsterNumber(?string $monsterNumber, ?TestResultSource $testResultSource): void
    {
        $case = $this->createCase(testMonsterNumber: $monsterNumber, testResultSource: $testResultSource);

        if ($monsterNumber !== null) {
            $this->answersForCase($case)->assertAnswer(new Answer('NCOVmonnr', $monsterNumber));
        } else {
            $this->answersForCase($case)->assertEmpty();
        }
    }

    public function testItReturnsTheTestMonsterNumberOnTheCaseOverTheLatestTestResultMonsterNumber(): void
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();

        $case->created_at = CarbonImmutable::now();
        $case->source = TestResultSource::publicWebPortal();
        $case->testMonsterNumber = Str::random(11);

        $answers = $this->answersForCase($case);
        $answers->assertAnswer(new Answer('NCOVmonnr', $case->testMonsterNumber));
    }

    public function testItReturnsNullWhenCaseSourceCoronit(): void
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();

        $case->created_at = CarbonImmutable::now();
        $case->source = TestResultSource::coronit()->value;
        $case->testMonsterNumber = Str::random(11);

        $answers = $this->answersForCase($case);
        $answers->assertCount(0);
    }

    public function testItReturnsNullWhenNoCoronitNoCaseMonsterNumberAndNoTestResults(): void
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();

        $case->created_at = CarbonImmutable::now();
        $case->source = TestResultSource::publicWebPortal();

        $this->answersForCase($case)->assertEmpty();
    }

    public function testItReturnsTheMonsterNumberOfTheLatestTestResultWhenNoCoronitAndNoCaseMonsterNumber(): void
    {
        Event::fake();
        $monsterNumber = Str::random(11);
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();

        $this->mock(TestResultRepository::class, static function (MockInterface $mock) use ($monsterNumber): void {
            $testResult = TestResult::newInstanceWithVersion(1);
            $testResult->monsterNumber = $monsterNumber;
            $testResult->source = TestResultSource::meldportaal();
            $mock->allows('latestPositiveForCase')->andReturns($testResult);
        });

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVmonnr', $monsterNumber));
    }

    public function testItReturnsNullWhenNoCoronitAndNoCaseMonsterNumber(): void
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();

        $case->created_at = CarbonImmutable::now();
        $case->source = TestResultSource::publicWebPortal();
        $case->testMonsterNumber = null;

        $this->answersForCase($case)->assertEmpty();
    }

    public static function monsterNumberDataProvider(): array
    {
        return [
            'Monsternumber present for source CorronIT' => [null, TestResultSource::coronit()],
            'Monsternumber present for source MeldPortaal' => [Str::random(), TestResultSource::meldportaal()],
            'Monsternumber present for empty source' => [Str::random(), null],
        ];
    }

    private function createCase(?string $testMonsterNumber, ?TestResultSource $testResultSource): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();

        $case->created_at = CarbonImmutable::now();
        $case->source = $testResultSource?->value;
        $case->testMonsterNumber = $testMonsterNumber;

        return $case;
    }
}
