<?php

declare(strict_types=1);

namespace Tests\Unit\Models\CovidCase\OsirisValidation;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\CovidCase\Hospital;
use App\Schema\Validation\ValidationRule;
use App\Schema\Validation\ValidationRules;
use MinVWS\DBCO\Enum\Models\HospitalReason;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\FakerHelper;
use Tests\TestCase;

#[Group('osiris')]
#[Group('osiris-validation')]
#[Group('hospital-fragment')]
class HospitalTest extends TestCase
{
    use ValidatesModels;

    #[DataProvider('admittedAtDataProvider')]
    public function testValidationAdmittedAt(array $data, array $additionalData, bool $passes, string $severityLevel = ValidationRules::WARNING): void
    {
        $validatedData = [];

        $data = FakerHelper::populateWithDateTimes($this->faker, $data);
        $additionalData = FakerHelper::populateWithDateTimes($this->faker, $additionalData);

        $validationResult = $this->validateModel(
            Hospital::class,
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

    public static function admittedAtDataProvider(): array
    {
        return [
            'validation passes' => [
                [
                    'admittedAt' => FakerHelper::getPastDateAfter('-2 weeks'),
                ],
                [],
                true,
            ],
            'validation fails when admittedAt in the future' => [
                [
                    'admittedAt' => FakerHelper::getDateBetween('1 day', '10 days'),
                ],
                [],
                false,
            ],
            'validation fails when admittedAt before date of test' => [
                [
                    'admittedAt' => FakerHelper::getPastDateAfter('-6 months'),
                ],
                [
                    'test-dateOfTest' => FakerHelper::getDateBefore('-7 months'),
                ],
                false,
                ValidationRules::NOTICE,
            ],
            'validation fails when admittedInICUAt before date of test' => [
                [
                    'admittedInICUAt' => FakerHelper::getPastDateAfter('-6 months'),
                ],
                [
                    'test-dateOfTest' => FakerHelper::getDateBefore('-7 months'),
                ],
                false,
                ValidationRules::NOTICE,
            ],
            'validation fails when admittedAt before date of start surveillance' => [
                [
                    'admittedAt' => FakerHelper::getDateBefore('-6 years'),
                ],
                [
                    'startOfCovidSurveillanceDate' => FakerHelper::getDate('2020-03-01'),
                ],
                false,
            ],
            'validation fails when admittedAt before date of date of birth' => [
                [
                    'admittedAt' => FakerHelper::getDateBetween('-6 days', '-3 days'),
                ],
                [
                    'index-dateOfBirth' => FakerHelper::getPastDateAfter('-2 days'),
                ],
                false,
            ],
        ];
    }

    #[DataProvider('isAdmittedDataProvider')]
    public function testValidationIsAdmitted(array $data, array $additionalData, bool $passes, string $severityLevel = ValidationRules::WARNING, ?string $field = null): void
    {
        $validatedData = [];

        $additionalData = FakerHelper::populateWithDateTimes($this->faker, $additionalData);

        $validationResult = $this->validateModel(
            Hospital::class,
            $data,
            $validatedData,
            $severityLevel,
            $additionalData,
            [
                ValidationRule::TAG_OSIRIS_FINAL,
            ],
        );
        $passes ? $this->assertEmpty($validationResult) : $this->assertHasSeverityAndField($severityLevel, $field, $validationResult);
    }

    public static function isAdmittedDataProvider(): array
    {
        return [
            'validation passes is Admitted' => [
                [
                    'isAdmitted' => YesNoUnknown::no()->value,
                ],
                [],
                true,
            ],
            'validation passes isAdmitted yes' => [
                [
                    'isAdmitted' => YesNoUnknown::yes()->value,
                    'reason' => HospitalReason::covid()->value,
                    'isInICU' => YesNoUnknown::no()->value,
                ],
                [],
                true,
            ],
            // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX START
            // 'validation fails when isAdmitted without reason given' => [
            //     [
            //         'isAdmitted' => YesNoUnknown::yes()->value,
            //         'isInICU' => YesNoUnknown::yes()->value,
            //     ],
            //     [],
            //     false,
            //     ValidationRules::WARNING,
            //     'reason',
            // ],
            // 'validation fails when isAdmitted without isAtICU' => [
            //     [
            //         'isAdmitted' => YesNoUnknown::yes()->value,
            //         'reason' => HospitalReason::covid()->value,
            //     ],
            //     [],
            //     false,
            //     ValidationRules::WARNING,
            //     'isInICU',
            // ],
            // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX END
            'validation fails when index is isIntICU and younger than 18' => [
                [
                    'isAdmitted' => YesNoUnknown::yes()->value,
                    'reason' => HospitalReason::covid()->value,
                    'isInICU' => YesNoUnknown::yes()->value,
                ],
                [
                    'index-dateOfBirth' => FakerHelper::getPastDateAfter('-18 years'),
                ],
                false,
                ValidationRules::NOTICE,
                'isInICU',
            ],
            'validation fails when index is admitted at hospital and younger than 4' => [
                [
                    'isAdmitted' => YesNoUnknown::yes()->value,
                    'reason' => HospitalReason::covid()->value,
                    'isInICU' => YesNoUnknown::no()->value,
                ],
                [
                    'index-dateOfBirth' => FakerHelper::getPastDateAfter('-4 years'),
                ],
                false,
                ValidationRules::NOTICE,
                'isAdmitted',
            ],
        ];
    }

    public function assertHasSeverityAndField(string $severityLevel, ?string $field, array $validationResult): void
    {
        $this->assertArrayHasKey($severityLevel, $validationResult);
        if ($field) {
            $this->assertArrayHasKey($field, $validationResult[$severityLevel]['failed']);
        }
    }
}
