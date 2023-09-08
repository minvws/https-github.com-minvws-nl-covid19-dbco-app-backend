<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVwinsteltypeOvBuilder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\RiskLocationType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function assert;

#[Builder(NCOVwinsteltypeOvBuilder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVwinsteltypeOvBuilderTest extends TestCase
{
    use AssertAnswers;

    public function testIsOtherTypeAndLivingAtRiskLocation(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), RiskLocationType::other());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwinsteltypeOv', $case->riskLocation->otherType));
    }

    public function testIsNotOtherType(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), RiskLocationType::prison());
        $this->answersForCase($case)->assertEmpty();
    }

    public function testIsNotLivingAtRiskLocation(): void
    {
        $case = $this->createCase(YesNoUnknown::no(), RiskLocationType::other());
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(?YesNoUnknown $isLivingAtRiskLocation, RiskLocationType $riskLocationType): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);

        $case->createdAt = CarbonImmutable::now();
        $case->riskLocation->isLivingAtRiskLocation = $isLivingAtRiskLocation;
        $case->riskLocation->type = $riskLocationType;
        $case->riskLocation->otherType = $this->faker->name();

        return $case;
    }
}
