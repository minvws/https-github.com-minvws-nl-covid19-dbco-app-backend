<?php

declare(strict_types=1);

namespace Tests\Unit\Models\CovidCase\OsirisValidation;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\CovidCase\Deceased;
use App\Schema\Validation\ValidationRule;
use App\Schema\Validation\ValidationRules;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\FakerHelper;
use Tests\TestCase;

use function array_key_exists;

#[Group('osiris')]
#[Group('osiris-validation')]
class DeceasedTest extends TestCase
{
    use ValidatesModels;

    /**
     * @throws ValidationException
     */
    #[DataProvider('deceasedAtDataProvider')]
    public function testValidationDeceasedAtAfterDateOfSymptomOnsetAndDateOfTest(
        array $input,
        array $additionalData,
        bool $shouldValidate,
        string $severityLevel = ValidationRules::WARNING,
    ): void {
        $input = FakerHelper::populateWithDateTimes($this->faker, $input);
        $additionalData['caseCreationDate'] = FakerHelper::getDate('today');
        if (!array_key_exists('index-dateOfBirth', $additionalData)) {
            $additionalData['index-dateOfBirth'] = FakerHelper::getDateBefore('-50 years');
        }

        $additionalData = FakerHelper::populateWithDateTimes($this->faker, $additionalData);

        $validatedData = [];
        $validationResult = $this->validateModel(
            Deceased::class,
            $input,
            $validatedData,
            $severityLevel,
            $additionalData,
            [ValidationRule::TAG_OSIRIS_FINAL],
        );
        $shouldValidate
            ? $this->assertEmpty($validationResult)
            : $this->assertArrayHasKey($severityLevel, $validationResult);
    }

    public static function deceasedAtDataProvider(): array
    {
        return [
            'validation passes' => [
                [
                    'deceasedAt' => ['-3 days', '-2 days'],
                ],
                [
                    'test-dateOfSymptomOnset' => FakerHelper::getDateBetween('-100 days', '-4 days'),
                    'test-dateOfTest' => FakerHelper::getDateBetween('-100 days', '-5 days'),
                ],
                true,
            ],
            'validation passes when no dateOfSymptomsOrDateOfTest' => [
                [
                    'deceasedAt' => FakerHelper::getDateBetween('-3 days', '-2 days'),
                ],
                [],
                true,
            ],
            'validation fails when deceaseAt before start of Covid surveillance date' => [
                [
                    'deceasedAt' => FakerHelper::getDateBefore('2020-03-01'),
                ],
                [
                    'startOfCovidSurveillanceDate' => FakerHelper::getDate('2020-03-01'),
                ],
                false,
            ],
            'validation fails when deceasedAt in the future' => [
                [
                    'deceasedAt' => FakerHelper::getDateBetween('2 days', '6 days'),
                ],
                [],
                false,
            ],
            'validation fails on dateOfSymptomOnset' => [
                [
                    'deceasedAt' => FakerHelper::getDateBetween('-3 days', '-2 days'),
                ],
                [
                    'test-dateOfSymptomOnset' => FakerHelper::getPastDateAfter('-1 day'),
                ],
                false,
            ],
            'validation fails on dateOfTest' => [
                [
                    'deceasedAt' => FakerHelper::getDateBetween('-3 days', '-2 days'),
                ],
                [
                    'test-dateOfTest' => FakerHelper::getPastDateAfter('-1 day'),
                ],
                false,
            ],
            'validation fails on dateOfTest while dateOfSymptomOnset is okay' => [
                [
                    'deceasedAt' => FakerHelper::getDateBetween('-3 days', '-2 days'),
                ],
                [
                    'test-dateOfSymptomOnset' => FakerHelper::getDateBetween('-100 days', '-4 days'),
                    'test-dateOfTest' => FakerHelper::getPastDateAfter('-1 day'),
                ],
                false,
            ],
            'validation fails when after admitted at hospital' => [
                [
                    'deceasedAt' => FakerHelper::getDateBefore('-4 days'),
                ],
                [
                    'hospital-admittedInICUAt' => FakerHelper::getPastDateAfter('-3 days'),
                ],
                false,
            ],
            'validation fails when after admitted at ICU' => [
                [
                    'deceasedAt' => FakerHelper::getDateBefore('-4 days'),
                ],
                [
                    'hospital-admittedInICUAt' => FakerHelper::getPastDateAfter('-3 days'),
                ],
                false,
            ],
            'validation notice fails when deceased younger as 45 years' => [
                [
                    'deceasedAt' => FakerHelper::getDateBefore('-4 days'),
                ],
                [
                    'index-dateOfBirth' => FakerHelper::getPastDateAfter('-30 years'),
                ],
                false,
                ValidationRules::NOTICE,
            ],
            'validation notice passes when deceased older as 45 years' => [
                [
                    'deceasedAt' => FakerHelper::getPastDateAfter('-4 days'),
                ],
                [
                    'index-dateOfBirth' => FakerHelper::getDateBefore('-46 years'),
                ],
                true,
            ],
            'validation notice passes when deceased today' => [
                [
                    'deceasedAt' => FakerHelper::getDate('today'),
                ],
                [
                    'index-dateOfBirth' => FakerHelper::getDateBefore('-46 years'),
                ],
                true,
            ],
        ];
    }

    // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX START
    // public function testValidationShouldFailWhenIndexIsDeceasedAndCareProfessional(): void
    // {
    //     $validatedData = [];
    //     $validationResult = $this->validateModel(
    //         Deceased::class,
    //         [
    //             'isDeceased' => YesNoUnknown::yes()->value,
    //             'deceasedAt' => $this->faker->dateTimeBetween('-10 days', '-5 days')->format('Y-m-d'),
    //         ],
    //         $validatedData,
    //         ValidationRules::NOTICE,
    //         [
    //             'isCareProfessional' => true,
    //             'caseCreationDate' => CarbonImmutable::now()->format('Y-m-d'),
    //             'index-dateOfBirth' => CarbonImmutable::parse('-50 years')->format('Y-m-d'),
    //         ],
    //         [ValidationRule::TAG_OSIRIS_FINAL],
    //     );
    //     $this->assertArrayHasKey(ValidationRules::WARNING, $validationResult);
    // }
    // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX END

    public function testValidationShouldPassWhenIndexIsDeceasedAndDeceasedAtIsPresent(): void
    {
        $validatedData = [];
        $validationResult = $this->validateModel(
            Deceased::class,
            [
                'isDeceased' => YesNoUnknown::yes()->value,
                'deceasedAt' => $this->faker->dateTimeBetween('-10 days', '-5 days')->format('Y-m-d'),
            ],
            $validatedData,
            ValidationRules::NOTICE,
            [
                'caseCreationDate' => CarbonImmutable::now()->format('Y-m-d'),
                'index-dateOfBirth' => CarbonImmutable::parse('-50 years')->format('Y-m-d'),
                'underlyingSuffering-hasUnderlyingSuffering' => YesNoUnknown::no()->value,
            ],
            [ValidationRule::TAG_OSIRIS_FINAL],
        );

        $this->assertEmpty($validationResult);
    }

    // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX START
    // public function testValidationShouldNotPassWhenIndexIsDeceasedAndDeceasedAtIsEmpty(): void
    // {
    //     $validatedData = [];
    //     $validationResult = $this->validateModel(
    //         Deceased::class,
    //         [
    //             'isDeceased' => YesNoUnknown::yes(),
    //             'deceasedAt' => '',
    //         ],
    //         $validatedData,
    //         ValidationRules::NOTICE,
    //         [
    //             'caseCreationDate' => CarbonImmutable::now()->format('Y-m-d'),
    //             'index-dateOfBirth' => CarbonImmutable::parse('-50 years')->format('Y-m-d'),
    //         ],
    //         [ValidationRule::TAG_OSIRIS_FINAL],
    //     );

    //     $this->assertArrayHasKey(ValidationRules::WARNING, $validationResult);
    //     $this->assertEquals('isDeceased', $validationResult['warning']['failed']['deceasedAt']['RequiredIf'][0]);
    // }
    // ----------------------------------------------------------- PORTAL 2.0 OSIRIS VALIDATIE FIX END
}
