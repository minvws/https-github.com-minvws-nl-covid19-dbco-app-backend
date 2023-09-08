<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Osiris\Answer;

use App\Models\Eloquent\EloquentCase;
use App\Services\Osiris\Answer\Answer;
use App\Services\Osiris\Answer\NCOVwinsteltypeV3Builder;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\RiskLocationType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

#[Builder(NCOVwinsteltypeV3Builder::class)]
#[Group('osiris')]
#[Group('osiris-answer')]
class NCOVwinsteltypeV3BuilderTest extends TestCase
{
    use AssertAnswers;

    #[DataProvider('riskLocationDataProvider')]
    public function testRiskLocation(
        int $caseVersion,
        RiskLocationType $riskLocationType,
        string $expectedResult,
    ): void {
        $case = $this->createCase($caseVersion, YesNoUnknown::yes(), $riskLocationType);
        $this->answersForCase($case)->assertAnswer(new Answer('NCOVwinsteltypeV3', $expectedResult));
    }

    public static function riskLocationDataProvider(): array
    {
        return [
            '3, nursingHome' => [3, RiskLocationType::nursingHome(), '107'],
            '3, disabledResidentalCar' => [3, RiskLocationType::disabledResidentalCar(), '108'],
            '3, ggzInstitution' => [3, RiskLocationType::ggzInstitution(), '109'],
            '3, assistedLiving' => [3, RiskLocationType::assistedLiving(), '110'],
            '3, socialLiving' => [3, RiskLocationType::socialLiving(), '141'],
            '3, asylumCenter' => [3, RiskLocationType::asylumCenter(), '153'],
            '3, other' => [3, RiskLocationType::other(), '151'],

            '4, nursingHome' => [4, RiskLocationType::nursingHome(), '107'],
            '4, disabledResidentalCar' => [4, RiskLocationType::disabledResidentalCar(), '108'],
            '4, ggzInstitution' => [4, RiskLocationType::ggzInstitution(), '109'],
            '4, assistedLiving' => [4, RiskLocationType::assistedLiving(), '110'],
            '4, socialLiving' => [4, RiskLocationType::socialLiving(), '141'],
            '4, asylumCenter' => [4, RiskLocationType::asylumCenter(), '153'],
            '4, other' => [4, RiskLocationType::other(), '151'],
        ];
    }

    #[DataProvider('emptyDataProvider')]
    public function testRiskLocationNullRiskLocationTypeNotNull(
        int $caseVersion,
        ?YesNoUnknown $isLivingAtRiskLocation,
        ?RiskLocationType $riskLocationType,
    ): void {
        $case = $this->createCase($caseVersion, $isLivingAtRiskLocation, $riskLocationType);
        $this->answersForCase($case)->assertEmpty();
    }

    public static function emptyDataProvider(): array
    {
        return [
            '3, null, null' => [3, null, null],
            '3, yes, null' => [3, YesNoUnknown::yes(), null],
            '3, no, asylumCenter' => [3, YesNoUnknown::no(), RiskLocationType::asylumCenter()],
            '3, null, asylumCenter' => [3, null, RiskLocationType::asylumCenter()],

            '4, null, null' => [4, null, null],
            '4, yes, null' => [4, YesNoUnknown::yes(), null],
            '4, no, asylumCenter' => [4, YesNoUnknown::no(), RiskLocationType::asylumCenter()],
            '4, null, asylumCenter' => [4, null, RiskLocationType::asylumCenter()],
        ];
    }

    private function createCase(
        int $caseVersion,
        ?YesNoUnknown $isLivingAtRiskLocation,
        ?RiskLocationType $riskLocationType,
    ): EloquentCase {
        /** @var EloquentCase $case */
        $case = EloquentCase::getSchema()->getVersion($caseVersion)->newInstance();
        $case->createdAt = CarbonImmutable::now();

        $case->riskLocation->isLivingAtRiskLocation = $isLivingAtRiskLocation;
        $case->riskLocation->type = $riskLocationType;

        return $case;
    }
}
