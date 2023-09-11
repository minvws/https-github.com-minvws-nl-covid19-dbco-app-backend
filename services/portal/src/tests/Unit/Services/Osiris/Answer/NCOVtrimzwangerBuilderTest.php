<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVtrimzwangerBuilder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Generator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVtrimzwangerBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVtrimzwangerBuilderTest extends TestCase
{
    use AssertAnswers;

    public static function trimesterProvider(): Generator
    {
        yield 'Week 1 (1)' => [1, '1'];
        yield 'Week 12 (1)' => [12, '1'];
        yield 'Week 13 (2)' => [13, '2'];
        yield 'Week 20 (2)' => [20, '2'];
        yield 'Week 25 (2)' => [25, '2'];
        yield 'Week 26 (3)' => [26, '3'];
        yield 'Week 40 (3)' => [40, '3'];
        yield 'Week 43 (3)' => [43, '3'];
    }

    #[DataProvider('trimesterProvider')]
    public function testTrimester(int $week, string $expectedValue): void
    {
        $deceasedAt = CarbonImmutable::yesterday();
        $dueDate = $deceasedAt->clone()->addWeeks(40 - $week);
        $case = $this->createCase(YesNoUnknown::yes(), $deceasedAt, YesNoUnknown::yes(), $dueDate);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVtrimzwanger', $expectedValue));
    }

    public function testDeceasedAtNull(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), null, YesNoUnknown::yes(), CarbonImmutable::make('+2 months'));
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVtrimzwanger', '9'));
    }

    public function testDueDateNull(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), CarbonImmutable::yesterday(), YesNoUnknown::yes(), null);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVtrimzwanger', '9'));
    }

    public function testIsDeceasedNo(): void
    {
        $case = $this->createCase(
            YesNoUnknown::no(),
            CarbonImmutable::yesterday(),
            YesNoUnknown::yes(),
            CarbonImmutable::make('+2 months'),
        );
        $this->answersForCase($case)->assertEmpty();
    }

    public function testIsPregnantNo(): void
    {
        $case = $this->createCase(
            YesNoUnknown::yes(),
            CarbonImmutable::yesterday(),
            YesNoUnknown::no(),
            CarbonImmutable::make('+2 months'),
        );
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(YesNoUnknown $isDeceased, ?DateTimeInterface $deceasedAt, YesNoUnknown $isPregnant, ?DateTimeInterface $dueDate): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $case->deceased->isDeceased = $isDeceased;
        $case->deceased->deceasedAt = $deceasedAt;
        $case->pregnancy->isPregnant = $isPregnant;
        $case->pregnancy->dueDate = $dueDate;
        return $case;
    }
}
