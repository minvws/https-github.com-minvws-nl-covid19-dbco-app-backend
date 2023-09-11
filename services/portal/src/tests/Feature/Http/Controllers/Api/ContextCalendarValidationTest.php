<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CovidCase\Hospital;
use App\Models\CovidCase\Immunity;
use App\Models\CovidCase\Medication;
use App\Models\CovidCase\Symptoms;
use App\Models\CovidCase\Test;
use App\Models\CovidCase\UnderlyingSuffering;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\HospitalReason;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('context')]
#[Group('calendar')]
#[Group('validation')]
class ContextCalendarValidationTest extends FeatureTestCase
{
    #[DataProvider('calendarRulesMatrixLine1DataProvider')]
    #[DataProvider('calendarRulesMatrixLine2DataProvider')]
    #[DataProvider('calendarRulesMatrixLine3DataProvider')]
    #[DataProvider('calendarRulesMatrixLine4DataProvider')]
    #[DataProvider('calendarRulesMatrixLine5DataProvider')]
    #[DataProvider('calendarRulesMatrixLine6DataProvider')]
    public function testCreateContextValidateMomentDay(
        ?YesNoUnknown $hasSymptoms,
        ?YesNoUnknown $isImmune,
        ?YesNoUnknown $hasUnderlyingSufferingOrMedication,
        ?YesNoUnknown $isImmunoCompromised,
        ?YesNoUnknown $isAdmitted,
        ?HospitalReason $reason,
        string $inputDate,
        int $expectedStatusCode,
    ): void {
        CarbonImmutable::setTestNow('2021-10-01');

        $dateOfSymptomOnset = CarbonImmutable::createStrict(2021, 9, 1);
        $dateOfTest = CarbonImmutable::createStrict(2021, 9, 3);

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'test' => Test::newInstanceWithVersion(
                Test::getSchema()->getCurrentVersion()->getVersion(),
                static function (Test $test) use ($dateOfSymptomOnset, $dateOfTest): void {
                    $test->dateOfSymptomOnset = $dateOfSymptomOnset;
                    $test->dateOfTest = $dateOfTest;
                },
            ),
            'symptoms' => Symptoms::newInstanceWithVersion(2, static function (Symptoms $symptoms) use (
                $hasSymptoms,
            ): void {
                $symptoms->hasSymptoms = $hasSymptoms;
                $symptoms->symptoms = [
                    Symptom::coldShivers(),
                    Symptom::dizziness(),
                ];
            }),
            'immunity' => Immunity::newInstanceWithVersion(1, static function (Immunity $immunity) use ($isImmune): void {
                $immunity->isImmune = $isImmune;
            }),
            'underlying_suffering' => UnderlyingSuffering::newInstanceWithVersion(
                1,
                static function (UnderlyingSuffering $underlyingSuffering) use ($hasUnderlyingSufferingOrMedication): void {
                    $underlyingSuffering->hasUnderlyingSufferingOrMedication = $hasUnderlyingSufferingOrMedication;
                },
            ),
            'medication' => Medication::newInstanceWithVersion(
                1,
                static function (Medication $medication) use ($isImmunoCompromised): void {
                    $medication->isImmunoCompromised = $isImmunoCompromised;
                },
            ),
            'hospital' => Hospital::newInstanceWithVersion(
                1,
                static function (Hospital $hospital) use ($isAdmitted, $reason): void {
                    $hospital->isAdmitted = $isAdmitted;
                    $hospital->reason = $reason;
                },
            ),
        ]);
        $context = $this->createContextForCase($case);

        $response = $this->be($user)->putJson(sprintf('/api/contexts/%s/fragments', $context->uuid), [
            'general' => [
                'moments' => [
                    [
                        'day' => $inputDate,
                        'startTime' => null,
                        'endTime' => null,
                    ],
                ],
            ],
        ]);

        $response->assertStatus($expectedStatusCode);
    }

    public static function calendarRulesMatrixLine1DataProvider(): array
    {
        return [
            '1: isAdmitted yes. reason covid, 2021-08-31' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::yes(), // eloquentCase.hospital.isAdmitted
                HospitalReason::covid(), // eloquentCase.hospital.reason
                '2021-08-31', // input date
                200, // expected resultcode
            ],
            '1: isAdmitted yes. reason covid, 2021-09-08' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::yes(), // eloquentCase.hospital.isAdmitted
                HospitalReason::covid(), // eloquentCase.hospital.reason
                '2021-09-08', // input date
                200, // expected resultcode
            ],
            '1: isAdmitted yes. reason covid, 2021-09-11' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::yes(), // eloquentCase.hospital.isAdmitted
                HospitalReason::covid(), // eloquentCase.hospital.reason
                '2021-09-11', // input date
                200, // expected resultcode
            ],
            '1: isAdmitted yes. reason covid, 2021-09-12' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::yes(), // eloquentCase.hospital.isAdmitted
                HospitalReason::covid(), // eloquentCase.hospital.reason
                '2021-09-12', // input date
                400, // expected resultcode
            ],
        ];
    }

    public static function calendarRulesMatrixLine2DataProvider(): array
    {
        return [
            '2: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-08-17' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-08-17', // input date
                400, // expected resultcode
            ],
            '2: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-09-06' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-06', // input date
                200, // expected resultcode
            ],
            '2: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-09-07' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-07', // input date
                400, // expected resultcode
            ],
            '2: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-09-09' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-09', // input date
                400, // expected resultcode
            ],
            '2: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-09-16' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-16', // input date
                400, // expected resultcode
            ],
        ];
    }

    public static function calendarRulesMatrixLine3DataProvider(): array
    {
        return [
            '3: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-08-17' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-08-17', // input date
                400, // expected resultcode
            ],
            '3: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-09-06' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-06', // input date
                200, // expected resultcode
            ],
            '3: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-09-07' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-07', // input date
                400, // expected resultcode
            ],
            '3: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-09-16' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-16', // input date
                400, // expected resultcode
            ],
        ];
    }

    public static function calendarRulesMatrixLine4DataProvider(): array
    {
        return [
            '4: hasSymptoms yes, hasUnderlyingSufferingOrMedication yes, 2021-08-17' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::yes(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::yes(), // eloquentCase.medication.isImmunoCompromised
                null, // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-08-17', // input date
                400, // expected resultcode
            ],
            '4: hasSymptoms yes, hasUnderlyingSufferingOrMedication yes, 2021-09-06' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::yes(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::yes(), // eloquentCase.medication.isImmunoCompromised
                null, // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-06', // input date
                200, // expected resultcode
            ],
            '4: hasSymptoms yes, hasUnderlyingSufferingOrMedication yes, 2021-09-07' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::yes(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::yes(), // eloquentCase.medication.isImmunoCompromised
                null, // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-07', // input date
                400, // expected resultcode
            ],
        ];
    }

    public static function calendarRulesMatrixLine5DataProvider(): array
    {
        return [
            '5: hasSymptoms no, isImmune no, isAdmitted no, 2021-08-17' => [
                YesNoUnknown::no(), // eloquentCase.symptoms.hasSymptoms
                YesNoUnknown::no(), // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-08-10', // input date
                400, // expected resultcode
            ],
            '5: hasSymptoms no, isImmune no, isAdmitted no, 2021-08-31' => [
                YesNoUnknown::no(), // eloquentCase.symptoms.hasSymptoms
                YesNoUnknown::no(), // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-08-31', // input date
                200, // expected resultcode
            ],
            '5: hasSymptoms no, isImmune no, isAdmitted no, 2021-09-06' => [
                YesNoUnknown::no(), // eloquentCase.symptoms.hasSymptoms
                YesNoUnknown::no(), // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-06', // input date
                200, // expected resultcode
            ],
            '5: hasSymptoms no, isImmune no, isAdmitted no, 2021-09-07' => [
                YesNoUnknown::no(), // eloquentCase.symptoms.hasSymptoms
                YesNoUnknown::no(), // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-20', // input date
                400, // expected resultcode
            ],
        ];
    }

    public static function calendarRulesMatrixLine6DataProvider(): array
    {
        return [
            '6: hasSymptoms no, isImmune yes, isAdmitted no, 2021-08-17' => [
                YesNoUnknown::no(), // eloquentCase.symptoms.hasSymptoms
                YesNoUnknown::yes(), // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-08-15', // input date
                400, // expected resultcode
            ],
            '6: hasSymptoms no, isImmune yes, isAdmitted no, 2021-08-31' => [
                YesNoUnknown::no(), // eloquentCase.symptoms.hasSymptoms
                YesNoUnknown::yes(), // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-08-31', // input date
                200, // expected resultcode
            ],
            '6: hasSymptoms no, isImmune yes, isAdmitted no, 2021-09-06' => [
                YesNoUnknown::no(), // eloquentCase.symptoms.hasSymptoms
                YesNoUnknown::yes(), // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-06', // input date
                200, // expected resultcode
            ],
            '6: hasSymptoms no, isImmune yes, isAdmitted no, 2021-09-07' => [
                YesNoUnknown::no(), // eloquentCase.symptoms.hasSymptoms
                YesNoUnknown::yes(), // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-20', // input date
                400, // expected resultcode
            ],
        ];
    }

    #[DataProvider('hasUnderlyingSufferingDataProvider')]
    public function testCreateContextValidateMomentDayHasUnderlyingSuffering(
        ?YesNoUnknown $hasUnderlyingSuffering,
    ): void {
        CarbonImmutable::setTestNow('2021-10-01');

        $dateOfSymptomOnset = CarbonImmutable::createStrict(2021, 9, 1);
        $dateOfTest = CarbonImmutable::createStrict(2021, 9, 3);

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'test' => Test::newInstanceWithVersion(
                Test::getSchema()->getCurrentVersion()->getVersion(),
                static function (Test $test) use ($dateOfSymptomOnset, $dateOfTest): void {
                    $test->dateOfSymptomOnset = $dateOfSymptomOnset;
                    $test->dateOfTest = $dateOfTest;
                },
            ),
            'symptoms' => Symptoms::newInstanceWithVersion(2, static function (Symptoms $symptoms): void {
                $symptoms->hasSymptoms = YesNoUnknown::yes();
                $symptoms->symptoms = [
                    Symptom::coldShivers(),
                    Symptom::dizziness(),
                ];
            }),
            'immunity' => Immunity::newInstanceWithVersion(1, static function (Immunity $immunity): void {
                $immunity->isImmune = YesNoUnknown::no();
            }),
            'underlying_suffering' => UnderlyingSuffering::newInstanceWithVersion(
                1,
                static function (UnderlyingSuffering $underlyingSuffering) use ($hasUnderlyingSuffering): void {
                    $underlyingSuffering->hasUnderlyingSuffering = $hasUnderlyingSuffering;
                    $underlyingSuffering->hasUnderlyingSufferingOrMedication = YesNoUnknown::yes();
                },
            ),
            'medication' => Medication::newInstanceWithVersion(1, static function (Medication $medication): void {
                $medication->isImmunoCompromised = YesNoUnknown::yes();
            }),
            'hospital' => Hospital::newInstanceWithVersion(1, static function (Hospital $hospital): void {
                $hospital->isAdmitted = YesNoUnknown::no();
                $hospital->reason = null;
            }),
        ]);
        $context = $this->createContextForCase($case);

        $response = $this->be($user)->putJson(sprintf('/api/contexts/%s/fragments', $context->uuid), [
            'general' => [
                'moments' => [
                    [
                        'day' => '2021-09-06',
                        'startTime' => null,
                        'endTime' => null,
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200);
    }

    public static function hasUnderlyingSufferingDataProvider(): array
    {
        return [
            'yes' => [YesNoUnknown::yes()],
            'no' => [YesNoUnknown::no()],
            'unknown' => [YesNoUnknown::unknown()],
            'null' => [null],
        ];
    }
}
