<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\TestResult;
use App\Repositories\TestResultRepository;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVCoronITMonnrBuilder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use MinVWS\DBCO\Enum\Models\TestResultSource;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVCoronITMonnrBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVCoronITMonnrBuilderTest extends TestCase
{
    use AssertAnswers;

    #[DataProvider('monsterNumberDataProvider')]
    public function testMonsterNumber(?string $monsterNumber, ?TestResultSource $testResultSource): void
    {
        $case = $this->createCase($monsterNumber, $testResultSource);

        if ($monsterNumber !== null) {
            $this->answersForCase($case)->assertAnswer(new Answer('NCOVCoronITMonnr', $monsterNumber));
        } else {
            $this->answersForCase($case)->assertEmpty();
        }
    }

    public function testItReturnsTheCaseMonsterNumberWhenCaseSourceCoronit(): void
    {
        $case = $this->createCase('123456789', TestResultSource::coronit());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVCoronITMonnr', '123456789'));
    }

    public function testItDoesNotReturnTheLatestPositiveTestResultNumberWhenCaseSourceCoronit(): void
    {
        $case = $this->createCase('123456789', TestResultSource::coronit());


        $case->testResults = [
            TestResult::newInstanceWithVersion(1, static function (TestResult $testResult): void {
                $testResult->source = TestResultSource::coronit();
                $testResult->monster_number = '987654321';
                $testResult->test_date = CarbonImmutable::now();
            }),
        ];

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVCoronITMonnr', '123456789'));
    }

    public function testItReturnsTheLatestPositiveTestResultMonsterNumberWhenCaseSourceNotCoronit(): void
    {
        $case = $this->createCase('123456789', TestResultSource::meldportaal());
        $monsterNumber = (string) $this->faker->randomNumber(9);

        $this->partialMock(TestResultRepository::class, static function (MockInterface $mock) use ($monsterNumber): void {
            $mock->allows('latestPositiveForCase')->andReturns(
                TestResult::newInstanceWithVersion(1, static function (TestResult $testResult) use ($monsterNumber): void {
                    $testResult->source = TestResultSource::coronit();
                    $testResult->monster_number = $monsterNumber;
                    $testResult->test_date = CarbonImmutable::now();
                }),
            );
        });

        $this->answersForCase($case)->assertAnswer(new Answer('NCOVCoronITMonnr', $monsterNumber));
    }

    public static function monsterNumberDataProvider(): array
    {
        return [
            'Monsternumber present for source CorronIT' => [Str::random(), TestResultSource::coronit()],
            'Monsternumber present for source MeldPortaal' => [null, TestResultSource::meldportaal()],
            'Monsternumber present for empty source' => [null, null],
        ];
    }

    private function createCase(?string $monsterNumber, ?TestResultSource $testResultSource): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $case->source = $testResultSource?->value;
        $case->test_monster_number = $monsterNumber;
        return $case;
    }
}
