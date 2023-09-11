<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\ZIEDtOverlijdenBuilder;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(ZIEDtOverlijdenBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class ZIEDtOverlijdenBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testDeceasedAt(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), CarbonImmutable::make('May 21st 2022'));
        $this->answersForCase($case)->assertAnswer(new Answer('ZIEDtOverlijden', '21-05-2022'));
    }

    public function testDeceasedAtNull(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), null);
        $this->answersForCase($case)->assertEmpty();
    }

    public function testDeceasedAtIsDeceasedNo(): void
    {
        $case = $this->createCase(YesNoUnknown::no(), CarbonImmutable::make('May 21st 2022'));
        $this->answersForCase($case)->assertEmpty();
    }

    public function testDeceasedAtIsDeceasedUnknown(): void
    {
        $case = $this->createCase(YesNoUnknown::unknown(), CarbonImmutable::make('May 21st 2022'));
        $this->answersForCase($case)->assertEmpty();
    }

    public function testDeceasedAtIsDeceasedNull(): void
    {
        $case = $this->createCase(null, CarbonImmutable::make('May 21st 2022'));
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(?YesNoUnknown $isDeceased, ?DateTimeInterface $deceasedAt): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $case->deceased->isDeceased = $isDeceased;
        $case->deceased->deceasedAt = $deceasedAt;
        return $case;
    }
}
