<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Policy\PolicyGuideline;

use App\Exceptions\Policy\PolicyFactMissingException;
use App\Models\Policy\PolicyGuideline;
use App\Services\Policy\IndexPolicyFacts;
use App\Services\Policy\PolicyGuideline\PolicyGuidelineHandler;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use MinVWS\DBCO\Enum\Models\PolicyGuidelineReferenceField;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('policy')]
#[Group('policyGuideline')]
class IndexPolicyGuidelineHandlerTest extends FeatureTestCase
{
    #[DataProvider('applyDataProvider')]
    public function testApply(array $policyGuidelineData, ?array $expectedResult): void
    {
        $policyGuideline = PolicyGuideline::factory()->create($policyGuidelineData);
        $policyGuidelineHandler = new PolicyGuidelineHandler($policyGuideline);

        $firstSickDate = CarbonImmutable::create(2021, 5, 27);
        $facts = IndexPolicyFacts::create(
            YesNoUnknown::yes(),
            null,
            YesNoUnknown::yes(),
            null,
            null,
        )->withDateOfSymptomOnset($firstSickDate);


        $expectedSourcePeriod = new CarbonPeriod(
            CarbonImmutable::parse($expectedResult['source_start_date']),
            CarbonImmutable::parse($expectedResult['source_end_date']),
        );
        $expectedContagioursPeriod = new CarbonPeriod(
            CarbonImmutable::parse($expectedResult['contagious_start_date']),
            CarbonImmutable::parse($expectedResult['contagious_end_date']),
        );

        $this->assertEquals($expectedSourcePeriod, $policyGuidelineHandler->calculateSourcePeriod($facts));
        $this->assertEquals($expectedContagioursPeriod, $policyGuidelineHandler->calculateContagiousPeriod($facts));
    }

    public static function applyDataProvider(): array
    {
        return [
            'Basic calculation' => [
                [
                    'source_start_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                    'source_start_date_addition' => -10,
                    'source_end_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                    'source_end_date_addition' => -2,
                    'contagious_start_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                    'contagious_start_date_addition' => -2,
                    'contagious_end_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
                    'contagious_end_date_addition' => 5,
                ],
                [
                    'source_start_date' => '2021-5-17',
                    'source_end_date' => '2021-5-25',
                    'contagious_start_date' => '2021-5-25',
                    'contagious_end_date' => '2021-6-1',
                ],
            ],
        ];
    }

    public function testApplyWithMissingFactDataShouldThrowAnException(): void
    {
        $policyGuideline = PolicyGuideline::factory()->create([
            'source_start_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
            'source_start_date_addition' => -10,
            'source_end_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
            'source_end_date_addition' => -2,
            'contagious_start_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
            'contagious_start_date_addition' => -2,
            'contagious_end_date_reference' => PolicyGuidelineReferenceField::dateOfSymptomOnset(),
            'contagious_end_date_addition' => 5,
        ]);
        $policyGuidelineHandler = new PolicyGuidelineHandler($policyGuideline);

        $factsWithoutFirstSickDate = IndexPolicyFacts::create(
            YesNoUnknown::yes(),
            null,
            YesNoUnknown::yes(),
            null,
        );

        $this->expectException(PolicyFactMissingException::class);
        $policyGuidelineHandler->calculateSourcePeriod($factsWithoutFirstSickDate);
    }
}
