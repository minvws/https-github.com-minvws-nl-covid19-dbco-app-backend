<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVtrimzwangerBuilder;
use Carbon\Carbon;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVtrimzwangerBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVtrimzwangerV2BuilderTest extends TestCase
{
    use AssertAnswers;

    public function testDeceasedAtNull(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), null, YesNoUnknown::yes());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVtrimzwanger', '9'));
    }

    public function testIsDeceasedNo(): void
    {
        $case = $this->createCase(YesNoUnknown::no(), Carbon::yesterday(), YesNoUnknown::yes());
        $this->answersForCase($case)->assertEmpty();
    }

    public function testIsPregnantNo(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), Carbon::yesterday(), YesNoUnknown::no());
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(YesNoUnknown $isDeceased, ?DateTimeInterface $deceasedAt, YesNoUnknown $isPregnant): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(5)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = Carbon::now();
        $case->deceased->isDeceased = $isDeceased;
        $case->deceased->deceasedAt = $deceasedAt;
        $case->pregnancy->isPregnant = $isPregnant;
        return $case;
    }
}
