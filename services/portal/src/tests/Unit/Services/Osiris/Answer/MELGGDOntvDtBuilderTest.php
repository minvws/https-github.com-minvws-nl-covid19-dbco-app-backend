<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\MELGGDOntvDtBuilder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(MELGGDOntvDtBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class MELGGDOntvDtBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testCreatedAt(): void
    {
        $case = $this->createCase(new CarbonImmutable("May 10th 2022"));
        $this->answersForCase($case)->assertAnswer(new Answer('MELGGDOntvDt', '10-05-2022'));
    }

    public function testCreatedAtNull(): void
    {
        $case = $this->createCase(null);
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(?DateTimeInterface $createdAt): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = $createdAt;
        return $case;
    }
}
