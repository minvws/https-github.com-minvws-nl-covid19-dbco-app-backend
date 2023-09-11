<?php

declare(strict_types=1);

namespace Tests\Unit\Models\CovidCase\OsirisValidation;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\CovidCase\Test;
use App\Schema\Validation\ValidationRule;
use App\Schema\Validation\ValidationRules;
use MinVWS\DBCO\Enum\Models\LabTestIndicator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\FakerHelper;
use Tests\TestCase;

#[Group('osiris')]
#[Group('osiris-validation')]
class TestTest extends TestCase
{
    use ValidatesModels;

    #[DataProvider('dateOfSymptomOnsetDataProvider')]
    public function testValidationDateOfSymptomOnset(array $data, array $additionalData, bool $passes, string $severityLevel = ValidationRules::WARNING): void
    {
        $validatedData = [];

        $data = FakerHelper::populateWithDateTimes($this->faker, $data);
        $additionalData = FakerHelper::populateWithDateTimes($this->faker, $additionalData);

        $validationResult = $this->validateModel(
            Test::class,
            $data,
            $validatedData,
            $severityLevel,
            $additionalData,
            [
                ValidationRule::TAG_OSIRIS_FINAL,
            ],
        );
        $passes ? $this->assertEmpty($validationResult) : $this->assertArrayHasKey($severityLevel, $validationResult);
    }

    public static function dateOfSymptomOnsetDataProvider(): array
    {
        return [
            'validation passes' => [
                [
                    'dateOfSymptomOnset' => FakerHelper::getDateBetween('-3 days', '-2 days'),
                ],
                [],
                true,
            ],
            'validation fails when admittedAtHospital is set before dateOfSymptomOnset' => [
                [
                    'dateOfSymptomOnset' => FakerHelper::getPastDateAfter('-3 days'),
                ],
                [
                    'hospital-admittedAt' => FakerHelper::getDateBetween('-9 days', '-4 days'),
                ],
                false,
                ValidationRules::NOTICE,
            ],
            'validation fails when admittedInICUAt is set before dateOfSymptomOnset' => [
                [
                    'dateOfSymptomOnset' => FakerHelper::getPastDateAfter('-3 days'),
                ],
                [
                    'hospital-admittedInICUAt' => FakerHelper::getDateBetween('-9 days', '-4 days'),
                ],
                false,
                ValidationRules::NOTICE,
            ],
            'validation fails when dateOfSymptomsOnset is before first allowable date' => [
                [
                    'dateOfSymptomOnset' => FakerHelper::getDateBefore('-4 years'),
                ],
                [
                    'firstAllowableDateOfSymptomOnset' => FakerHelper::getDate('2020-01-01'),
                ],
                false,
            ],
            'validation fails when dateOfSymptomsOnset is after deceased' => [
                [
                    'dateOfSymptomOnset' => FakerHelper::getPastDateAfter('-2 days'),
                ],
                [
                    'deceased-deceasedAt' => FakerHelper::getDateBefore('-4 days'),
                ],
                false,
            ],
            'validation fails when dateOfSymptomsOnset is after date of birth' => [
                [
                    'dateOfSymptomOnset' => FakerHelper::getDateBefore('-5 months'),
                ],
                [
                    'index-dateOfBirth' => FakerHelper::getPastDateAfter('-4 months'),
                ],
                false,
            ],
            'validation fails when dateOfResult before start of Covid surveillance date' => [
                [
                    'dateOfResult' => FakerHelper::getDateBefore('-4 years'),
                ],
                [
                    'startOfCovidSurveillanceDate' => FakerHelper::getDate('2020-03-01'),
                ],
                false,
            ],
            'validation passes when dateOfResult after dateOfSymptomOnset' => [
                [
                    'dateOfResult' => FakerHelper::getDate('-1 week'),
                ],
                [
                    'test-dateOfSymptomOnset' => FakerHelper::getDateBetween('-4 weeks', '-1 week'),
                ],
                true,
            ],
            'validation passes when dateOfResult less than 21 days after dateOfSymptomOnset' => [
                [
                    'dateOfResult' => FakerHelper::getDate('+20 days'),
                ],
                [
                    'test-dateOfSymptomOnset' => FakerHelper::getDate('today'),
                ],
                true,
            ],
            'validation fails when dateOfResult more than 21 days after dateOfSymptomOnset' => [
                [
                    'dateOfResult' => FakerHelper::getDate('+22 days'),
                ],
                [
                    'test-dateOfSymptomOnset' => FakerHelper::getDate('today'),
                ],
                false,
                ValidationRules::NOTICE,
            ],
            'validation passes when dateOfSymptomOnset before dateOfResult' => [
                [
                    'dateOfSymptomOnset' => FakerHelper::getDateBetween('-27 days', '-7 days'),
                ],
                [
                    'test-dateOfResult' => FakerHelper::getDate('-1 week'),
                ],
                true,
            ],
            'validation passes when dateOfSymptomOnset less than 21 days before dateOfResult' => [
                [
                    'dateOfSymptomOnset' => FakerHelper::getDate('today'),
                ],
                [
                    'test-dateOfResult' => FakerHelper::getDate('+20 days'),
                ],
                true,
            ],
            'validation fails when dateOfSymptomOnset more than 21 days before dateOfResult' => [
                [
                    'dateOfSymptomOnset' => FakerHelper::getDate('today'),
                ],
                [
                    'test-dateOfResult' => FakerHelper::getDate('+22 days'),
                ],
                false,
                ValidationRules::NOTICE,
            ],
            'validation fails when dateOfSymptomOnset within 8 weeks of previous infection' => [
                [
                    'dateOfSymptomOnset' => FakerHelper::getPastDateAfter('-6 days'),
                ],
                [
                    'test-previousInfectionDateOfSymptom' => FakerHelper::getDateBetween('-8 weeks', '-1 week'),
                ],
                false,
                ValidationRules::NOTICE,
            ],
            'validation fails when dateOfSymptomOnset before previous infection' => [
                [
                    'dateOfSymptomOnset' => FakerHelper::getDateBetween('-10 weeks', '-9 weeks'),
                ],
                [
                    'test-previousInfectionDateOfSymptom' => FakerHelper::getDateBetween('-8 weeks', '-1 week'),
                ],
                false,
                ValidationRules::WARNING,
            ],
            'validation fails when previous infection after dateOfSymptomOnset ' => [
                [
                    'previousInfectionDateOfSymptom' => FakerHelper::getDateBetween('-8 weeks', '-1 week'),
                ],
                [
                    'test-dateOfSymptomOnset' => FakerHelper::getDateBetween('-10 weeks', '-9 weeks'),
                ],
                false,
                ValidationRules::WARNING,
            ],
            'validation fails with notice when dateOfSymptomOnset is after case creation date' => [
                [
                    'dateOfSymptomOnset' => FakerHelper::getPastDateAfter('-5 days'),
                ],
                [
                    'caseCreationDate' => FakerHelper::getDateBetween('-2 weeks', '-1 week'),
                ],
                false,
                ValidationRules::NOTICE,
            ],
            'validation fails with notice when selfTestLabTestDate after dateOfSymptomOnset ' => [
                [
                    'selfTestLabTestDate' => FakerHelper::getDateBetween('-2 weeks', '-1 week'),
                ],
                [
                    'test-dateOfSymptomOnset' => FakerHelper::getPastDateAfter('-5 days'),
                    'maxBeforeCaseCreationDate' => FakerHelper::getDate('-100 days'),
                ],
                false,
                ValidationRules::NOTICE,
            ],
            'validation fails when dateOfResult after dateOfSymptomOnset' => [
                [
                    'dateOfResult' => FakerHelper::getPastDateAfter('-3 days'),
                ],
                [
                    'test-dateOfSymptomOnset' => FakerHelper::getDateBefore('-4 days'),
                ],
                false,
                ValidationRules::NOTICE,
            ],
        ];
    }

    #[DataProvider('otherLabTestIndicatorDataProvider')]
    public function testValidationOtherLabTestIndicator(array $data, bool $passes, array $filterTags = [
        ValidationRule::TAG_OSIRIS_FINAL,
    ],): void
    {
        $validatedData = [];

        $validationResult = $this->validateModel(
            Test::class,
            $data,
            $validatedData,
            ValidationRules::WARNING,
            [],
            $filterTags,
        );

        $passes ? $this->assertEmpty($validationResult) : $this->assertArrayHasKey(ValidationRules::WARNING, $validationResult);
    }

    public static function otherLabTestIndicatorDataProvider(): array
    {
        return [
            'validation passes for final notification' => [
                [
                    'labTestIndicator' => LabTestIndicator::other()->value,
                    'otherLabTestIndicator' => 'foo',
                ],
                true,
            ],
            'validation passes for intial notification' => [
                [
                    'labTestIndicator' => LabTestIndicator::other()->value,
                    'otherLabTestIndicator' => 'foo',
                ],
                true,
                [ValidationRule::TAG_OSIRIS_INITIAL],

            ],
            // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX START
//             'validation fails when otherLabTestIndictor is empty for final notification' => [
//                 [
//                     'labTestIndicator' => LabTestIndicator::other()->value,
//                     'otherLabTestIndicator' => '',
//                 ],
//                 false,
//             ],
//             'validation fails when otherLabTestIndictor is empty for initial notification' => [
//                 [
//                     'labTestIndicator' => LabTestIndicator::other()->value,
//                     'otherLabTestIndicator' => '',
//                 ],
//                 false,
//                 [ValidationRule::TAG_OSIRIS_INITIAL],
//             ],
            // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX END
            'validation passes when labTestIndictor is not set to `other` for final notification' => [
                [
                    'labTestIndicator' => LabTestIndicator::antigen()->value,
                    'otherLabTestIndicator' => '',
                ],
                true,
            ],
        ];
    }
}
