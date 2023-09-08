<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use Carbon\CarbonImmutable;
use Generator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Group('osiris')]
#[Group('osiris-answer')]
class MERSPATbuitenlBuilderTest extends TestCase
{
    use AssertAnswers;

    public static function wasAbroadProvider(): Generator
    {
        yield 'wasAbroad = yes' => [YesNoUnknown::yes(), 'J'];
        yield 'wasAbroad = no' => [YesNoUnknown::no(), 'N'];
        yield 'wasAbroad = unknown' => [YesNoUnknown::unknown(), 'Onb'];
    }

    #[DataProvider('wasAbroadProvider')]
    public function testWasAbroad(YesNoUnknown $wasAbroad, string $value): void
    {
        $case = $this->createCase($wasAbroad);
        $this->answersForCase($case)->assertAnswer(new Answer('MERSPATbuitenl', $value));
    }

    public function testWasAbroadNull(): void
    {
        $case = $this->createCase(null);
        $this->answersForCase($case)->assertCount(0);
    }

    private function createCase(?YesNoUnknown $wasAbroad): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $case->abroad->wasAbroad = $wasAbroad;
        return $case;
    }
}
