<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVwinsteltypeV2Builder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\RiskLocationType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

use function array_unique;
use function assert;
use function count;

#[Builder(NCOVwinsteltypeV2Builder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVwinsteltypeV2BuilderTest extends TestCase
{
    use AssertAnswers;

    public function testRiskLocationTypeMappings(): void
    {
        $values = [];

        foreach (RiskLocationType::all() as $riskLocationType) {
            $case = $this->createCase(YesNoUnknown::yes(), $riskLocationType);
            $answers = $this->answersForCase($case);
            $answers->assertCount(1, 'No valid risk location type mapping for ' . $riskLocationType->label);
            $this->assertEquals('NCOVwinsteltypeV2', $answers[0]->code);
            $this->assertIsNumeric($answers[0]->value);
            $values[] = $answers[0]->value;
        }

        $values = array_unique($values);
        $this->assertEquals(count(RiskLocationType::all()), count($values));
    }

    public function testIsLivingAtRiskLocationYes(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), RiskLocationType::asylumCenter());
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwinsteltypeV2', '152'));
    }

    public function testIsLivingAtRiskLocationNo(): void
    {
        $case = $this->createCase(YesNoUnknown::no(), RiskLocationType::asylumCenter());
        $this->answersForCase($case)->assertEmpty();
    }

    public function testRiskLocationTypeNull(): void
    {
        $case = $this->createCase(YesNoUnknown::yes(), null);
        $this->answersForCase($case)->assertEmpty();
    }

    private function createCase(?YesNoUnknown $isLivingAtRiskLocation, ?RiskLocationType $riskLocationType): EloquentCase
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof EloquentCase);
        $case->createdAt = CarbonImmutable::now();
        $case->riskLocation->isLivingAtRiskLocation = $isLivingAtRiskLocation;
        $case->riskLocation->type = $riskLocationType;
        return $case;
    }
}
