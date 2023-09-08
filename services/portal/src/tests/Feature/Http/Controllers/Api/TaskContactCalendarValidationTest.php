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

#[Group('task-contact-calendar')]
#[Group('calendar')]
#[Group('validation')]
class TaskContactCalendarValidationTest extends FeatureTestCase
{
    #[DataProvider('calendarRulesMatrixLine1DataProvider')]
    #[DataProvider('calendarRulesMatrixLine2DataProvider')]
    #[DataProvider('calendarRulesMatrixLine3DataProvider')]
    #[DataProvider('calendarRulesMatrixLine4DataProvider')]
    #[DataProvider('calendarRulesMatrixLine5DataProvider')]
    #[DataProvider('calendarRulesMatrixLine6DataProvider')]
    public function testCreateTaskValidateDateOfLastExposure(
        ?YesNoUnknown $hasSymptoms,
        ?YesNoUnknown $isImmune,
        ?YesNoUnknown $hasUnderlyingSufferingOrMedication,
        ?YesNoUnknown $isImmunoCompromised,
        ?YesNoUnknown $isAdmitted,
        ?HospitalReason $reason,
        string $dateOfLastExposure,
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
            'symptoms' => Symptoms::newInstanceWithVersion(1, static function (Symptoms $symptoms) use (
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
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
        ]);

        $response = $this->be($user)->putJson(sprintf('/api/tasks/%s', $task->uuid), [
            'task' => [
                'taskType' => 'contact',
                'dateOfLastExposure' => $dateOfLastExposure,
            ],
        ]);

        $response->assertStatus($expectedStatusCode);
    }

    public static function calendarRulesMatrixLine1DataProvider(): array
    {
        return [
            '1: isAdmitted yes. reason covid, 2021-08-29' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::yes(), // eloquentCase.hospital.isAdmitted
                HospitalReason::covid(), // eloquentCase.hospital.reason
                '2021-08-29', // dateOfLastExposure
                422, // expected resultcode
            ],
            '1: isAdmitted yes. reason covid, 2021-09-08' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::yes(), // eloquentCase.hospital.isAdmitted
                HospitalReason::covid(), // eloquentCase.hospital.reason
                '2021-09-08', // dateOfLastExposure
                200, // expected resultcode
            ],
            '1: isAdmitted yes. reason covid, 2021-09-11' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::yes(), // eloquentCase.hospital.isAdmitted
                HospitalReason::covid(), // eloquentCase.hospital.reason
                '2021-09-11', // dateOfLastExposure
                200, // expected resultcode
            ],
            '1: isAdmitted yes. reason covid, 2021-09-12' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::yes(), // eloquentCase.hospital.isAdmitted
                HospitalReason::covid(), // eloquentCase.hospital.reason
                '2021-09-12', // dateOfLastExposure
                422, // expected resultcode
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
                '2021-08-17', // dateOfLastExposure
                422, // expected resultcode
            ],
            '2: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-09-01' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-01', // dateOfLastExposure
                200, // expected resultcode
            ],
            '2: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-09-06' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-06', // dateOfLastExposure
                200, // expected resultcode
            ],
            '2: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no 2021-09-07' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-07', // dateOfLastExposure
                422, // expected resultcode
            ],
            '2: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-09-09' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-09', // dateOfLastExposure
                422, // expected resultcode
            ],
            '2: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-09-16' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-16', // dateOfLastExposure
                422, // expected resultcode
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
                '2021-08-17', // dateOfLastExposure
                422, // expected resultcode
            ],
            '3: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-09-01' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-01', // dateOfLastExposure
                200, // expected resultcode
            ],
            '3: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-09-06' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-06', // dateOfLastExposure
                200, // expected resultcode
            ],
            '3: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-09-07' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-07', // dateOfLastExposure
                422, // expected resultcode
            ],
            '3: hasSymptoms yes, hasUnderlyingSufferingOrMedication no, isAdmitted no, 2021-09-16' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::no(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::no(), // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-16', // dateOfLastExposure
                422, // expected resultcode
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
                '2021-08-17', // dateOfLastExposure
                422, // expected resultcode
            ],
            '4: hasSymptoms yes, hasUnderlyingSufferingOrMedication yes, 2021-09-06' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::yes(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::yes(), // eloquentCase.medication.isImmunoCompromised
                null, // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-06', // dateOfLastExposure
                200, // expected resultcode
            ],
            '4: hasSymptoms yes, hasUnderlyingSufferingOrMedication yes, 2021-09-07' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::yes(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::yes(), // eloquentCase.medication.isImmunoCompromised
                null, // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-07', // dateOfLastExposure
                422, // expected resultcode
            ],
            '4: hasSymptoms yes, hasUnderlyingSufferingOrMedication yes, 2021-09-16' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                null, // eloquentCase.immunity.isImmune
                YesNoUnknown::yes(), // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                YesNoUnknown::yes(), // eloquentCase.medication.isImmunoCompromised
                null, // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-16', // dateOfLastExposure
                422, // expected resultcode
            ],
        ];
    }

    public static function calendarRulesMatrixLine5DataProvider(): array
    {
        return [
            '5: hasSymptoms yes, isImmune no, isAdmitted no, 2021-08-17' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                YesNoUnknown::no(), // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-08-15', // dateOfLastExposure
                422, // expected resultcode
            ],
            '5: hasSymptoms yes, isImmune no, isAdmitted no, 2021-08-31' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                YesNoUnknown::no(), // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-08-31', // dateOfLastExposure
                200, // expected resultcode
            ],
            '5: hasSymptoms yes, isImmune no, isAdmitted no, 2021-09-06' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                YesNoUnknown::no(), // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-06', // dateOfLastExposure
                200, // expected resultcode
            ],
            '5: hasSymptoms yes, isImmune no, isAdmitted no, 2021-09-07' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                YesNoUnknown::no(), // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-07', // dateOfLastExposure
                422, // expected resultcode
            ],
        ];
    }

    public static function calendarRulesMatrixLine6DataProvider(): array
    {
        return [
            '6: hasSymptoms yes, isImmune yes, isAdmitted no, 2021-08-17' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                YesNoUnknown::yes(), // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-08-17', // dateOfLastExposure
                422, // expected resultcode
            ],
            '6: hasSymptoms yes, isImmune yes, isAdmitted no, 2021-08-31' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                YesNoUnknown::yes(), // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-08-31', // dateOfLastExposure
                200, // expected resultcode
            ],
            '6: hasSymptoms yes, isImmune yes, isAdmitted no, 2021-09-06' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                YesNoUnknown::yes(), // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-06', // dateOfLastExposure
                200, // expected resultcode
            ],
            '6: hasSymptoms yes, isImmune yes, isAdmitted no, 2021-09-07' => [
                YesNoUnknown::yes(), // eloquentCase.symptoms.hasSymptoms
                YesNoUnknown::yes(), // eloquentCase.immunity.isImmune
                null, // eloquentCase.underlying_suffering.hasUnderlyingSufferingOrMedication
                null, // eloquentCase.medication.isImmunoCompromised
                YesNoUnknown::no(), // eloquentCase.hospital.isAdmitted
                null, // eloquentCase.hospital.reason
                '2021-09-07', // dateOfLastExposure
                422, // expected resultcode
            ],
        ];
    }
}
