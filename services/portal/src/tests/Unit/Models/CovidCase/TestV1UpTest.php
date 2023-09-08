<?php

declare(strict_types=1);

namespace Tests\Unit\Models\CovidCase;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\CovidCase\Test;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\LabTestIndicator;
use MinVWS\DBCO\Enum\Models\PreviousInfectionReason;
use MinVWS\DBCO\Enum\Models\SelfTestIndicator;
use MinVWS\DBCO\Enum\Models\TestReason;
use MinVWS\DBCO\Enum\Models\TestResult;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

use function config;
use function sprintf;

#[Group('case-fragment-test')]
#[Group('fragment')]
final class TestV1UpTest extends TestCase
{
    use ValidatesModels;

    public function testWithInvalidPayloadShouldNotValidate(): void
    {
        $validationResult = $this->validateModel(Test::class, [
            'dateOfInfectiousnessStart' => 123,
            'dateOfSymptomOnset' => 123,
            'dateOfTest' => 123,
            'dateOfResult' => 123,
            'infectionIndicator' => "wrong value",
            'selfTestIndicator' => "wrong value",
            'labTestIndicator' => "wrong value",
            'isReinfection' => "wrong value",
            'isSymptomOnsetEstimated' => "wrong value",
            'monsterNumber' => 123,
            'otherReason' => 123,
            'previousInfectionDateOfSymptom' => 123,
            'previousInfectionCaseNumber' => 123,
            'previousInfectionSymptomFree' => 123,
            'reasons' => "wrong value",
            'previousInfectionProven' => "wrong value",
            'contactOfConfirmedInfection' => "wrong value",
            'previousInfectionReported' => "wrong value",
        ]);

        $this->assertArrayHasKey('failed', $validationResult['fatal']);

        $expectedFailingProperties = [
            'dateOfInfectiousnessStart',
            'dateOfSymptomOnset',
            'dateOfTest',
            'dateOfResult',
            'infectionIndicator',
            'selfTestIndicator',
            'labTestIndicator',
            'isReinfection',
            'isSymptomOnsetEstimated',
            'monsterNumber',
            'otherReason',
            'previousInfectionDateOfSymptom',
            'previousInfectionCaseNumber',
            'previousInfectionSymptomFree',
            'reasons',
            'previousInfectionProven',
            'contactOfConfirmedInfection',
            'previousInfectionReported',
        ];

        foreach ($expectedFailingProperties as $expectedFailingProperty) {
            $this->assertArrayHasKey(
                $expectedFailingProperty,
                $validationResult['fatal']['failed'],
                sprintf('expected field %s to fail', $expectedFailingProperty),
            );
        }
    }

    public function testWithMinimalPayloadShouldValidate(): void
    {
        $validationResult = $this->validateModel(Test::class, [
            'dateOfTest' => CarbonImmutable::now()->subDays(3)->format('Y-m-d'),
            'caseCreationDate' => CarbonImmutable::now()->format('Y-m-d'),
            'maxBeforeCaseCreationDate' => CarbonImmutable::now()->sub(
                config('misc.validations.maxBeforeCaseCreationDateInDays') . ' days',
            )->format(
                'Y-m-d',
            ),
        ]);
        $this->assertEmpty($validationResult);
    }

    public function testWithMinimalAndNullablePayloadShouldValidate(): void
    {
        $validationResult = $this->validateModel(Test::class, [
            'dateOfTest' => CarbonImmutable::yesterday()->format('Y-m-d'),
            'dateOfInfectiousnessStart' => null,
            'dateOfSymptomOnset' => null,
            'dateOfResult' => null,
            'infectionIndicator' => null,
            'selfTestIndicator' => null,
            'labTestIndicator' => null,
            'isReinfection' => null,
            'isSymptomOnsetEstimated' => null,
            'otherReason' => null,
            'previousInfectionDateOfSymptom' => null,
            'previousInfectionCaseNumber' => null,
            'previousInfectionSymptomFree' => null,
            'reasons' => null,
            'previousInfectionProven' => null,
            'contactOfConfirmedInfection' => null,
            'previousInfectionReported' => null,
            'caseCreationDate' => CarbonImmutable::now()->format('Y-m-d'),
            'maxBeforeCaseCreationDate' => CarbonImmutable::now()->sub(
                config('misc.validations.maxBeforeCaseCreationDateInDays') . ' days',
            )->format(
                'Y-m-d',
            ),
        ]);
        $this->assertEmpty($validationResult);
    }

    public function testWithValidPayloadShouldValidate(): void
    {
        $validationResult = $this->validateModel(Test::class, [
            'dateOfInfectiousnessStart' => CarbonImmutable::now()->subDays(3)->format('Y-m-d'),
            'dateOfSymptomOnset' => CarbonImmutable::now()->subDays(1)->format('Y-m-d'),
            'dateOfTest' => CarbonImmutable::now()->subDays(3)->format('Y-m-d'),
            'dateOfResult' => CarbonImmutable::now()->subDays(2)->format('Y-m-d'),
            'infectionIndicator' => InfectionIndicator::selfTest()->value,
            'selfTestIndicator' => SelfTestIndicator::antigen()->value,
            'labTestIndicator' => LabTestIndicator::molecular()->value,
            'isReinfection' => YesNoUnknown::yes()->value,
            'isSymptomOnsetEstimated' => true,
            'monsterNumber' => "123456",
            'otherReason' => "some other reason",
            'previousInfectionDateOfSymptom' => CarbonImmutable::now()->subDays(30)->format('Y-m-d'),
            'previousInfectionCaseNumber' => "123456",
            'previousInfectionOtherReasons' => ["reason1", "reason2"],
            'previousInfectionReason' => PreviousInfectionReason::contact()->value,
            'previousInfectionSymptomFree' => true,
            'reasons' => [TestReason::contact()->value],
            'previousInfectionProven' => YesNoUnknown::yes()->value,
            'contactOfConfirmedInfection' => true,
            'previousInfectionReported' => YesNoUnknown::yes()->value,
            'caseCreationDate' => CarbonImmutable::now()->format('Y-m-d'),
            'maxBeforeCaseCreationDate' => CarbonImmutable::now()->sub(
                config('misc.validations.maxBeforeCaseCreationDateInDays') . ' days',
            )->format(
                'Y-m-d',
            ),
            'selfTestLabTestDate' => CarbonImmutable::now()->addDays(7)->format('Y-m-d'),
            'selfTestLabTestResult' => TestResult::positive()->value,
        ]);

        $this->assertEmpty($validationResult);
    }

    public function testReasonsCoronamelderShouldNotValidate(): void
    {
        $validationResult = $this->validateModel(Test::class, [
            'dateOfTest' => CarbonImmutable::now()->subDays(3)->format('Y-m-d'),
            'caseCreationDate' => CarbonImmutable::now()->format('Y-m-d'),
            'maxBeforeCaseCreationDate' => CarbonImmutable::now()->sub(
                config('misc.validations.maxBeforeCaseCreationDateInDays') . ' days',
            )->format(
                'Y-m-d',
            ),
            'reasons' => [TestReason::coronamelder()->value],
        ]);

        $this->assertArrayHasKey(
            'reasons.0',
            $validationResult['fatal']['failed'],
            sprintf('expected field %s to fail', 'reasons.0'),
        );
    }

    public function testWithDateOfTestInFutureShouldNotValidate(): void
    {
        $validationResult = $this->validateModel(Test::class, [
            'dateOfTest' => CarbonImmutable::tomorrow()->format('Y-m-d'),
            'caseCreationDate' => CarbonImmutable::now()->format('Y-m-d'),
            'maxBeforeCaseCreationDate' => CarbonImmutable::now()->sub(
                config('misc.validations.maxBeforeCaseCreationDateInDays') . ' days',
            )->format(
                'Y-m-d',
            ),
        ]);
        $this->assertArrayHasKey('failed', $validationResult['warning']);
    }

    public function testWithDateOfTestAfterCaseCreationShouldNotValidate(): void
    {
        $validationResult = $this->validateModel(Test::class, [
            'dateOfTest' => CarbonImmutable::now()->addDays(2)->format('Y-m-d'),
            'caseCreationDate' => CarbonImmutable::now()->subDays(3)->format('Y-m-d'),
            'maxBeforeCaseCreationDate' => CarbonImmutable::now()->sub(
                config('misc.validations.maxBeforeCaseCreationDateInDays') . ' days',
            )->format(
                'Y-m-d',
            ),
        ]);
        $this->assertArrayHasKey('BeforeOrEqual', $validationResult['warning']['failed']['dateOfTest']);
    }

    public function testWithDateOfTestBeforeMaxCaseCreationShouldNotValidate(): void
    {
        $validationResult = $this->validateModel(Test::class, [
            'dateOfTest' => CarbonImmutable::now()->subDays(config('misc.validations.maxBeforeCaseCreationDateInDays') + 1)->format(
                'Y-m-d',
            ),
            'caseCreationDate' => CarbonImmutable::now()->format('Y-m-d'),
            'maxBeforeCaseCreationDate' => CarbonImmutable::now()->sub(
                config('misc.validations.maxBeforeCaseCreationDateInDays') . ' days',
            )->format(
                'Y-m-d',
            ),
        ]);
        $this->assertArrayHasKey('AfterOrEqual', $validationResult['warning']['failed']['dateOfTest']);
    }

    public function testWithSelfTestLabTestDateAfterTodayShouldNotValidate(): void
    {
        $validationResult = $this->validateModel(Test::class, [
            'maxBeforeCaseCreationDate' => CarbonImmutable::now()->sub('0 days')->format('Y-m-d'),
            'dateOfTest' => CarbonImmutable::now()->format('Y-m-d'),
            'selfTestLabTestDate' => CarbonImmutable::now()->addDays(8)->format('Y-m-d'),
        ]);
        $this->assertArrayHasKey('BeforeOrEqual', $validationResult['warning']['failed']['selfTestLabTestDate']);
        $this->assertEquals(
            [CarbonImmutable::now()->modify('+7 days')->format('Y-m-d')],
            $validationResult['warning']['failed']['selfTestLabTestDate']['BeforeOrEqual'],
        );
    }

    public function testWithSelfTestLabTestDateButWithoutTestDateShouldValidate(): void
    {
        $validationResult = $this->validateModel(Test::class, [
            'maxBeforeCaseCreationDate' => CarbonImmutable::now()->sub('100 days')->format('Y-m-d'),
            'selfTestLabTestDate' => CarbonImmutable::now()->subDays(6)->format('Y-m-d'),
        ]);
        $this->assertEmpty($validationResult);
    }
}
