<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVwerkand15mBuilder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVwerkand15mBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVwerkand15mBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testYes(): void
    {
        $case = $this->createCase(YesNoUnknown::yes());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwerkand15m', 'J'));
    }

    public function testNull(): void
    {
        $case = $this->createCase(null);
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(?YesNoUnknown $closeContactAtJob): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $case->job->closeContactAtJob = $closeContactAtJob;
        return $case;
    }
}
