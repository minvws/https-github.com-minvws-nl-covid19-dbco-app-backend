<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVgebdatBuilder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVgebdatBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVgebdatBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testDateOfBirth(): void
    {
        $case = $this->createCase(CarbonImmutable::make('1955-03-14'));
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVgebdat', '14-03-1955'));
    }

    public function testDateOfBirthNull(): void
    {
        $case = $this->createCase(null);
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(?DateTimeInterface $dateOfBirth): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $case->index->dateOfBirth = $dateOfBirth;
        return $case;
    }
}
