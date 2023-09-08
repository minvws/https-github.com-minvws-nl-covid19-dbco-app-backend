<?php

declare(strict_types=1);

namespace Tests\Unit\Models\CovidCase\OsirisValidation;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\CovidCase\Job;
use App\Schema\Validation\ValidationRule;
use App\Schema\Validation\ValidationRules;
use MinVWS\DBCO\Enum\Models\JobSector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\FakerHelper;
use Tests\TestCase;

#[Group('osiris')]
#[Group('osiris-validation')]
class JobTest extends TestCase
{
    use ValidatesModels;

    #[DataProvider('sectorsDataProvider')]
    public function testValidationSectors(array $data, array $additionalData, bool $passes, string $severityLevel = ValidationRules::WARNING): void
    {
        $validatedData = [];

        $additionalData = FakerHelper::populateWithDateTimes($this->faker, $additionalData);

        $validationResult = $this->validateModel(
            Job::class,
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

    public static function sectorsDataProvider(): array
    {
        return [
            'validation passes' => [
                [
                    'sectors' => [JobSector::ziekenhuis()->value],
                ],
                [
                    'index-dateOfBirth' => FakerHelper::getDateBetween('-64 years', '-17 years'),
                ],
                true,
            ],
            'validation passes when no sectors chosen' => [
                [
                    'sectors' => [],
                ],
                [
                    'index-dateOfBirth' => FakerHelper::getDateBetween('-64 years', '-17 years'),
                ],
                true,
            ],
            'validation passes for non Care professional' => [
                [
                    'sectors' => [JobSector::horeca()->value],
                ],
                [
                    'index-dateOfBirth' => FakerHelper::getDateBetween('-64 years', '-17 years'),
                ],
                true,
            ],
            // 'validation fails when older than 65' => [
            //     [
            //         'sectors' => [JobSector::ziekenhuis()->value],
            //     ],
            //     [
            //         'index-dateOfBirth' => FakerHelper::getDateBefore('-66 years'),
            //     ],
            //     false,
            //     ValidationRules::NOTICE,
            // ],
            // 'validation fails when younger than 16' => [
            //     [
            //         'sectors' => [JobSector::ziekenhuis()->value],
            //     ],
            //     [
            //         'index-dateOfBirth' => FakerHelper::getPastDateAfter('-15 years'),
            //     ],
            //     false,
            //     ValidationRules::NOTICE,
            // ],
        ];
    }
}
