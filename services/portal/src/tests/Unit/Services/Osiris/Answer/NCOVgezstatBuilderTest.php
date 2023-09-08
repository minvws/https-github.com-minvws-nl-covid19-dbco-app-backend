<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVgezstatBuilder;
use Carbon\CarbonImmutable;
use Generator;
use MinVWS\DBCO\Enum\Models\CauseOfDeath;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVgezstatBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVgezstatBuilderTest extends TestCase
{
    use AssertAnswers;

    public static function deceasedProvider(): Generator
    {
        yield [YesNoUnknown::yes(), CauseOfDeath::covid19(), '3'];
        yield [YesNoUnknown::yes(), CauseOfDeath::other(), '4'];
        yield [YesNoUnknown::yes(), CauseOfDeath::unknown(), '5'];
        yield [YesNoUnknown::yes(), null, '5'];

        foreach (CauseOfDeath::all() as $causeOfDeath) {
            yield [YesNoUnknown::no(), $causeOfDeath, '7']; // no
            yield [YesNoUnknown::unknown(), $causeOfDeath, '6']; // unknown
        }

        yield [YesNoUnknown::no(), null, '7']; // no
        yield [YesNoUnknown::unknown(), null, '6']; // unknown
        yield [null, null, '6']; // unknown
    }

    #[DataProvider('deceasedProvider')]
    public function testDeceased(?YesNoUnknown $isDeceased, ?CauseOfDeath $causeOfDeath, string $expectedValue): void
    {
        $case = $this->createCase($isDeceased, $causeOfDeath);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVgezstat', $expectedValue));
    }

    private function createCase(?YesNoUnknown $isDeceased, ?CauseOfDeath $causeOfDeath): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $case->deceased->isDeceased = $isDeceased;
        $case->deceased->cause = $causeOfDeath;
        return $case;
    }
}
