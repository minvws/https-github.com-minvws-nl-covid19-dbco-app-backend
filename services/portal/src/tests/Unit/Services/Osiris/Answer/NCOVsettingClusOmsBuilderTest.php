<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\NCOVsettingClusOmsBuilder;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVsettingClusOmsBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVsettingClusOmsBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testBuilderNotImplementedYet(): void
    {
        $case = $this->createCase();
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        return $case;
    }
}
