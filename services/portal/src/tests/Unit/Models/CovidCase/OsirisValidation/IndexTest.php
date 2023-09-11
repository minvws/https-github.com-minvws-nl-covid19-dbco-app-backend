<?php

declare(strict_types=1);

namespace Tests\Unit\Models\CovidCase\OsirisValidation;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\CovidCase\Index;
use App\Schema\Validation\ValidationRule;
use App\Schema\Validation\ValidationRules;
use Illuminate\Support\MessageBag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\FakerHelper;
use Tests\TestCase;

#[Group('osiris')]
#[Group('osiris-validation')]
class IndexTest extends TestCase
{
    use ValidatesModels;

    #[DataProvider('dateOfSymptomOnsetDataProvider')]
    public function testValidationDateOfBirth(array $data, array $additionalData, bool $passes, string $severityLevel = ValidationRules::WARNING): void
    {
        $validatedData = [];

        $data = FakerHelper::populateWithDateTimes($this->faker, $data);
        $additionalData = FakerHelper::populateWithDateTimes($this->faker, $additionalData);

        $validationResult = $this->validateModel(
            Index::class,
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
                    'dateOfBirth' => FakerHelper::getDateBetween('-64 years', '-17 years'),
                ],
                [
                    'caseCreationDate' => FakerHelper::getPastDateAfter('-2 days'),
                ],
                true,
            ],
            'validation fails when date of birth in the future' => [
                [
                    'dateOfBirth' => FakerHelper::getDateBetween('1 year', '10 years'),
                ],
                [],
                false,
            ],
            'validation fails when date of birth on same date as case creation' => [
                [
                    'dateOfBirth' => FakerHelper::getDate('today'),
                ],
                [
                    'caseCreationDate' => FakerHelper::getDate('today'),
                ],
                false,
            ],
            'validation fails when date of birth after date of symptom onset' => [
                [
                    'dateOfBirth' => FakerHelper::getPastDateAfter('-6 months'),
                ],
                [
                    'test-dateOfSymptomOnset' => FakerHelper::getDateBefore('-7 months'),
                ],
                false,
            ],
            'validation fails when date of birth after admitted at hospital' => [
                [
                    'dateOfBirth' => FakerHelper::getPastDateAfter('-6 months'),
                ],
                [
                    'hospital-admittedAt' => FakerHelper::getDateBefore('-7 months'),
                ],
                false,
            ],
            'validation fails when date of after case creation' => [
                [
                    'dateOfBirth' => FakerHelper::getPastDateAfter('-6 months'),
                ],
                [
                    'caseCreationDate' => FakerHelper::getDateBefore('-7 months'),
                ],
                false,
            ],
            'validation fails when date of birth before first allowed dateOfBirth' => [
                [
                    'dateOfBirth' => FakerHelper::getDate('1889-01-01'),
                ],
                [],
                false,
            ],
            'validation fails when care professional is younger as 16' => [
                [
                    'dateOfBirth' => FakerHelper::getPastDateAfter('-16 years'),
                ],
                [
                    'isCareProfessional' => true,
                ],
                false,
                ValidationRules::NOTICE,
            ],
        ];
    }

    #[DataProvider('careProfessionalDataProvider')]
    public function testValidationCareProfessionalDateOfBirth(array $data, array $additionalData, bool $passes, string $severityLevel = ValidationRules::WARNING, ?int $age = null): void
    {
        $validatedData = [];

        $data = FakerHelper::populateWithDateTimes($this->faker, $data);

        $validationResult = $this->validateModel(
            Index::class,
            $data,
            $validatedData,
            $severityLevel,
            $additionalData,
            [
                ValidationRule::TAG_OSIRIS_FINAL,
            ],
        );
        if ($passes) {
            $this->assertEmpty($validationResult);
        } else {
            $this->assertArrayHasKey($severityLevel, $validationResult);
            /** @var MessageBag $messageBog */
            $messageBog = $validationResult['notice']['errors'];
            $this->assertStringContainsString((string) $age, $messageBog->getMessages()['dateOfBirth'][0]);
        }
    }

    public static function careProfessionalDataProvider(): array
    {
        return [
            'validation fails when care professional is younger as 16' => [
                [
                    'dateOfBirth' => FakerHelper::getPastDateAfter('-16 years'),
                ],
                [
                    'isCareProfessional' => true,
                ],
                false,
                ValidationRules::NOTICE,
                16,
            ],
            'validation fails when care professional is older than 65' => [
                [
                    'dateOfBirth' => FakerHelper::getDateBetween('-100 years', '-66 years'),
                ],
                [
                    'isCareProfessional' => true,
                ],
                false,
                ValidationRules::NOTICE,
                65,
            ],
            'validation passes when care professional is between 16 and 65' => [
                [
                    'dateOfBirth' => FakerHelper::getDateBetween('-64 years', '-17 years'),
                ],
                [
                    'isCareProfessional' => true,
                ],
                true,
            ],
            'validation passes when not a care professional' => [
                [
                    'dateOfBirth' => FakerHelper::getDateBetween('-100 years', '-3 years'),
                ],
                [
                    'isCareProfessional' => false,
                ],
                true,
            ],
        ];
    }
}
