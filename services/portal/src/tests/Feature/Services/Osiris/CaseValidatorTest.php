<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Osiris;

use App\Models\CovidCase\Deceased;
use App\Models\CovidCase\Hospital;
use App\Models\CovidCase\Index;
use App\Models\CovidCase\Test;
use App\Models\CovidCase\Vaccination;
use App\Models\Shared\VaccineInjection;
use App\Models\Versions\CovidCase\Deceased\DeceasedV1;
use App\Models\Versions\CovidCase\Hospital\HospitalV1;
use App\Models\Versions\CovidCase\Index\IndexV2;
use App\Models\Versions\CovidCase\Test\TestV1;
use App\Models\Versions\CovidCase\Test\TestV2;
use App\Models\Versions\CovidCase\Vaccination\VaccinationV3;
use App\Models\Versions\Shared\VaccineInjection\VaccineInjectionV1;
use App\Services\Osiris\CaseValidator;
use Carbon\CarbonImmutable;
use Generator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function count;

#[Group('validation')]
class CaseValidatorTest extends FeatureTestCase
{
    #[DataProvider('caseValidatorDataProvider')]
    public function testValidation(array $caseAttributes, array $expectedErrors): void
    {
        $case = $this->createCase($caseAttributes);

        $caseValidator = new CaseValidator();
        $errors = $caseValidator->validate($case);

        $this->assertCount(count($expectedErrors), $errors);
        $this->assertEquals($expectedErrors, $errors->toArray());
    }

    public static function caseValidatorDataProvider(): Generator
    {
        yield 'no errors' => [
            [],
            [],
        ];

        yield 'createdAt_before_dateOfBirth' => [
            [
                'index' => Index::newInstanceWithVersion(2, static function (IndexV2 $indexV2): void {
                    $indexV2->dateOfBirth = CarbonImmutable::now()->addYear();
                }),
            ],
            ['createdAt_before_dateOfBirth'],
        ];

        yield 'admittedInICUAt_before_dateOfSymptomOnset' => [
            [
                'hospital' => Hospital::newInstanceWithVersion(1, static function (HospitalV1 $hospitalV1): void {
                    $hospitalV1->admittedInICUAt = CarbonImmutable::now()->subYear();
                }),
                'date_of_symptom_onset' => CarbonImmutable::now(),
            ],
            ['admittedInICUAt_before_dateOfSymptomOnset'],
        ];

        yield 'admittedAt_before_dateOfSymptomOnset' => [
            [
                'hospital' => Hospital::newInstanceWithVersion(1, static function (HospitalV1 $hospitalV1): void {
                    $hospitalV1->admittedAt = CarbonImmutable::now()->subYear();
                }),
                'date_of_symptom_onset' => CarbonImmutable::now(),
            ],
            ['admittedAt_before_dateOfSymptomOnset'],
        ];

        yield 'deceasedAt_before_dateOfSymptomOnset' => [
            [
                'deceased' => Deceased::newInstanceWithVersion(1, static function (DeceasedV1 $deceasedV1): void {
                    $deceasedV1->deceasedAt = CarbonImmutable::now()->subYear();
                }),
                'date_of_symptom_onset' => CarbonImmutable::now(),
            ],
            ['deceasedAt_before_dateOfSymptomOnset'],
        ];

        yield 'dateOfResult_before_dateOfSymptomOnset:testV1' => [
            ['test' => Test::newInstanceWithVersion(1)],
            [],
        ];

        yield 'dateOfResult_before_dateOfSymptomOnset:testV2' => [
            [
                'test' => Test::newInstanceWithVersion(2, static function (TestV2 $testV2): void {
                    $testV2->dateOfResult = CarbonImmutable::now()->subYear();
                }),
                'date_of_symptom_onset' => CarbonImmutable::now(),
            ],
            ['dateOfResult_before_dateOfSymptomOnset'],
        ];

        yield 'dateOfSymptomOnset_before_dateOfBirth' => [
            [
                'date_of_symptom_onset' => CarbonImmutable::now(),
                'index' => Index::newInstanceWithVersion(2, static function (IndexV2 $indexV2): void {
                    $indexV2->dateOfBirth = CarbonImmutable::now()->addYear();
                }),
            ],
            [
                'createdAt_before_dateOfBirth',
                'dateOfSymptomOnset_before_dateOfBirth',
            ],
        ];

        yield 'admittedAt_before_dateOfBirth' => [
            [
                'hospital' => Hospital::newInstanceWithVersion(1, static function (HospitalV1 $hospitalV1): void {
                    $hospitalV1->admittedAt = CarbonImmutable::now()->subYear();
                }),
                'index' => Index::newInstanceWithVersion(2, static function (IndexV2 $indexV2): void {
                    $indexV2->dateOfBirth = CarbonImmutable::now()->addYear();
                }),
            ],
            [
                'createdAt_before_dateOfBirth',
                'admittedAt_before_dateOfBirth',
            ],
        ];

        yield 'deceasedAt_before_hospitalAdmittedAt' => [
            [
                'deceased' => Deceased::newInstanceWithVersion(1, static function (DeceasedV1 $deceasedV1): void {
                    $deceasedV1->deceasedAt = CarbonImmutable::now()->subYear();
                }),
                'hospital' => Hospital::newInstanceWithVersion(1, static function (HospitalV1 $hospitalV1): void {
                    $hospitalV1->admittedAt = CarbonImmutable::now()->addYear();
                }),
            ],
            ['deceasedAt_before_hospitalAdmittedAt'],
        ];

        yield 'deceasedAt_before_admittedInICUAt' => [
            [
                'deceased' => Deceased::newInstanceWithVersion(1, static function (DeceasedV1 $deceasedV1): void {
                    $deceasedV1->deceasedAt = CarbonImmutable::now()->subYear();
                }),
                'hospital' => Hospital::newInstanceWithVersion(1, static function (HospitalV1 $hospitalV1): void {
                    $hospitalV1->admittedInICUAt = CarbonImmutable::now()->addYear();
                }),
            ],
            ['deceasedAt_before_admittedInICUAt'],
        ];

        yield 'deceasedAt_before_20200301' => [
            [
                'deceased' => Deceased::newInstanceWithVersion(1, static function (DeceasedV1 $deceasedV1): void {
                    $deceasedV1->deceasedAt = CarbonImmutable::createStrict(2020);
                }),
            ],
            ['deceasedAt_before_20200301'],
        ];

        yield 'createdAt_before_20200227' => [
            ['created_at' => CarbonImmutable::createStrict(2020)],
            ['createdAt_before_20200227'],
        ];

        yield 'admittedAt_before_20200301' => [
            [
                'hospital' => Hospital::newInstanceWithVersion(1, static function (HospitalV1 $hospitalV1): void {
                    $hospitalV1->admittedAt = CarbonImmutable::createStrict(2020);
                }),
            ],
            ['admittedAt_before_20200301'],
        ];

        yield 'dateOfResult_before_20200301:testV1' => [
            ['test' => Test::newInstanceWithVersion(1)],
            [],
        ];

        yield 'dateOfResult_before_20200301:testV2' => [
            [
                'test' => Test::newInstanceWithVersion(2, static function (TestV2 $testV2): void {
                    $testV2->dateOfResult = CarbonImmutable::createStrict(2020);
                }),
            ],
            ['dateOfResult_before_20200301'],
        ];

        yield 'dateOfBirth_before_19060101' => [
            [
                'index' => Index::newInstanceWithVersion(2, static function (IndexV2 $indexV2): void {
                    $indexV2->dateOfBirth = CarbonImmutable::createStrict(1900);
                }),
            ],
            ['dateOfBirth_before_19060101'],
        ];

        yield 'firstVaccineInjection_after_20210106:vaccinationV1' => [
            [
                'vaccination' => Vaccination::newInstanceWithVersion(1),
            ],
            [],
        ];

        yield 'firstVaccineInjection_after_20210106:vaccinationV2' => [
            [
                'vaccination' => Vaccination::newInstanceWithVersion(2),
            ],
            [],
        ];

        yield 'firstVaccineInjection_after_20210106:vaccinationV3' => [
            [
                'vaccination' => Vaccination::newInstanceWithVersion(3, static function (VaccinationV3 $vaccinationV3): void {
                    $vaccinationV3->vaccinationCount = 1;
                    $vaccinationV3->vaccineInjections = [
                        VaccineInjection::newInstanceWithVersion(1, static function (VaccineInjectionV1 $vaccineInjectionV1): void {
                            $vaccineInjectionV1->injectionDate = CarbonImmutable::createStrict(2021);
                        }),
                        VaccineInjection::newInstanceWithVersion(1, static function (VaccineInjectionV1 $vaccineInjectionV1): void {
                            $vaccineInjectionV1->injectionDate = CarbonImmutable::createStrict(2022);
                        }),
                    ];
                }),
            ],
            ['firstVaccineInjection_before_20210106'],
        ];

        yield 'firstVaccineInjection_after_20210106:vaccinationV3_vaccinationCount1_without_vaccineInjections' => [
            [
                'vaccination' => Vaccination::newInstanceWithVersion(3, static function (VaccinationV3 $vaccinationV3): void {
                    $vaccinationV3->vaccinationCount = 1;
                    $vaccinationV3->vaccineInjections = [];
                }),
            ],
            [],
        ];

        yield 'firstVaccineInjection_after_20210106:vaccinationV3_vaccinationCount2_without_vaccineInjections' => [
            [
                'vaccination' => Vaccination::newInstanceWithVersion(3, static function (VaccinationV3 $vaccinationV3): void {
                    $vaccinationV3->vaccinationCount = 2;
                    $vaccinationV3->vaccineInjections = [];
                }),
            ],
            [],
        ];

        yield 'firstVaccineInjection_after_20210106:vaccinationV3_vaccinationCount3_without_vaccineInjections' => [
            [
                'vaccination' => Vaccination::newInstanceWithVersion(3, static function (VaccinationV3 $vaccinationV3): void {
                    $vaccinationV3->vaccinationCount = 3;
                    $vaccinationV3->vaccineInjections = [];
                }),
            ],
            [],
        ];

        yield 'lastVaccineInjection_before_20210127&lastVaccineInjection_before_20210106:vaccinationV3' => [
            [
                'vaccination' => Vaccination::newInstanceWithVersion(3, static function (VaccinationV3 $vaccinationV3): void {
                    $vaccinationV3->vaccinationCount = 2;
                    $vaccinationV3->vaccineInjections = [
                        VaccineInjection::newInstanceWithVersion(1, static function (VaccineInjectionV1 $vaccineInjectionV1): void {
                            $vaccineInjectionV1->injectionDate = CarbonImmutable::createStrict(2021);
                        }),
                    ];
                }),
            ],
            [
                'lastVaccineInjection_before_20210127',
                'lastVaccineInjection_before_20210106',
            ],
        ];

        yield 'lastVaccineInjection_before_20210127:vaccinationV3' => [
            [
                'vaccination' => Vaccination::newInstanceWithVersion(3, static function (VaccinationV3 $vaccinationV3): void {
                    $vaccinationV3->vaccinationCount = 2;
                    $vaccinationV3->vaccineInjections = [
                        VaccineInjection::newInstanceWithVersion(1, static function (VaccineInjectionV1 $vaccineInjectionV1): void {
                            $vaccineInjectionV1->injectionDate = CarbonImmutable::createStrict(2021, 1, 10);
                        }),
                    ];
                }),
            ],
            ['lastVaccineInjection_before_20210127'],
        ];

        yield 'lastVaccineInjection_before_20210106:vaccinationV3' => [
            [
                'vaccination' => Vaccination::newInstanceWithVersion(3, static function (VaccinationV3 $vaccinationV3): void {
                    $vaccinationV3->vaccinationCount = 3;
                    $vaccinationV3->vaccineInjections = [
                        VaccineInjection::newInstanceWithVersion(1, static function (VaccineInjectionV1 $vaccineInjectionV1): void {
                            $vaccineInjectionV1->injectionDate = CarbonImmutable::createStrict(2021);
                        }),
                    ];
                }),
            ],
            ['lastVaccineInjection_before_20210106'],
        ];

        yield 'previousInfectionDateOfSymptom_between_20200101_and_createdAt:testV1_isReinfection_null' => [
            [
                'test' => Test::newInstanceWithVersion(1, static function (TestV1 $testV1): void {
                    $testV1->isReinfection = null;
                }),
            ],
            [],
        ];

        yield 'previousInfectionDateOfSymptom_between_20200101_and_createdAt:testV1' => [
            [
                'test' => Test::newInstanceWithVersion(1, static function (TestV1 $testV1): void {
                    $testV1->isReinfection = YesNoUnknown::yes();
                }),
            ],
            [],
        ];

        yield 'previousInfectionDateOfSymptom_between_20200101_and_createdAt:before2020:testV1' => [
            [
                'test' => Test::newInstanceWithVersion(1, static function (TestV1 $testV1): void {
                    $testV1->isReinfection = YesNoUnknown::yes();
                    $testV1->previousInfectionDateOfSymptom = CarbonImmutable::createStrict(2019);
                }),
            ],
            ['previousInfectionDateOfSymptom_before_20200101_or_after_createdAt'],
        ];

        yield 'previousInfectionDateOfSymptom_between_20200101_and_createdAt:afterCreatedAt:testV1' => [
            [
                'test' => Test::newInstanceWithVersion(1, static function (TestV1 $testV1): void {
                    $testV1->isReinfection = YesNoUnknown::yes();
                    $testV1->previousInfectionDateOfSymptom = CarbonImmutable::createStrict(2023);
                }),
                'created_at' => CarbonImmutable::createStrict(2022),
            ],
            ['previousInfectionDateOfSymptom_before_20200101_or_after_createdAt'],
        ];

        yield 'previousInfectionDateOfSymptom_between_20200101_and_createdAt:testV2' => [
            [
                'test' => Test::newInstanceWithVersion(2, static function (TestV2 $testV2): void {
                    $testV2->isReinfection = YesNoUnknown::yes();
                }),
            ],
            [],
        ];

        yield 'previousInfectionDateOfSymptom_between_20200101_and_createdAt:before2020:testV2' => [
            [
                'test' => Test::newInstanceWithVersion(2, static function (TestV2 $testV2): void {
                    $testV2->isReinfection = YesNoUnknown::yes();
                    $testV2->previousInfectionDateOfSymptom = CarbonImmutable::createStrict(2019);
                }),
            ],
            ['previousInfectionDateOfSymptom_before_20200101_or_after_createdAt'],
        ];

        yield 'previousInfectionDateOfSymptom_between_20200101_and_createdAt:afterCreatedAt:testV2' => [
            [
                'test' => Test::newInstanceWithVersion(2, static function (TestV2 $testV2): void {
                    $testV2->isReinfection = YesNoUnknown::yes();
                    $testV2->previousInfectionDateOfSymptom = CarbonImmutable::createStrict(2023);
                }),
                'created_at' => CarbonImmutable::createStrict(2022),
            ],
            ['previousInfectionDateOfSymptom_before_20200101_or_after_createdAt'],
        ];
    }
}
