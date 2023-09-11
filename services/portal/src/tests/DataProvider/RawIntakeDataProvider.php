<?php

declare(strict_types=1);

namespace Tests\DataProvider;

use function array_merge;
use function collect;

class RawIntakeDataProvider
{
    public static function validRawHandoverData(): array
    {
        return [
            'testMonsterNumber' => '123A456',
            'ggdRegion' => '12345',
        ];
    }

    public static function validRawIdentityData(): array
    {
        return [
            'censored_bsn' => '******789',
            'guid' => 'd7be5717-5f5b-4e1a-9d2e-fba43bd916f0',
            'pc3' => '123',
            'ggd_region' => '00000',
            'birth_date' => '1990-09-25',
            'first_name' => 'Frits',
            'last_name' => 'Smit',
            'prefix' => null,
            'gender' => 'V',
        ];
    }

    public static function validRawIntakeData(): array
    {
        return [
            'abroad' => [
                'wasAbroad' => 'unknown',
                'trips' => [],
            ],
            'housemates' => [
                'hasHouseMates' => 'unknown',
                'canStrictlyIsolate' => true,
            ],
            'job' => [
                'wasAtJob' => 'unknown',
                'sectors' => [],
            ],
            'pregnancy' => [
                'isPregnant' => 'unknown',
            ],
            'test' => [
                'dateOfSymptomOnset' => null,
                'isReinfection' => 'unknown',
                'previousInfectionDateOfSymptom' => null,
                'dateOfTest' => '2020-01-03',
                'infectionIndicator' => 'unknown',
            ],
            'symptoms' => [
                'hasSymptoms' => 'no', // cannot be unknown
                'symptoms' => [],
            ],
            'underlyingSuffering' => [
                'hasUnderlyingSuffering' => 'unknown',
                'hasUnderlyingSufferingOrMedication' => 'unknown',
                'items' => [],
            ],
            'vaccination' => [
                'isVaccinated' => 'yes',
                'vaccineInjections' => [],
            ],
            'meta' => [
                'cat1Count' => null,
                'estimatedCat2Count' => '2',
            ],
            'contacts' => [
                [
                    'general' => [
                        'reference' => '1234567',
                    ],
                ],
            ],
        ];
    }

    public static function validRawIntakeDataProvider(): array
    {
        $identityData = self::validRawIdentityData();
        $intakeData = self::validRawIntakeData();
        $handoverData = self::validRawHandoverData();

        return [
            'default' => [$identityData, $intakeData, $handoverData],
            'with contactdata' => [
                $identityData,
                array_merge($intakeData, [
                    'contact' => [
                        'email' => 'foo@bar.com',
                        'phone' => '0612345678',
                    ],
                ]),
                $handoverData,
            ],
            'minimal' => [
                collect($identityData)
                    ->put('first_name', null)
                    ->put('last_name', null)
                    ->put('prefix', null)
                    ->toArray(),
                [
                    'test' => [
                        'dateOfTest' => '2020-01-03',
                    ],
                ],
                null,
            ],
            'no handoverData' => [$identityData, $intakeData, null],
        ];
    }

    public static function invalidRawIntakeDataProvider(): array
    {
        $identityData = self::validRawIdentityData();
        $intakeData = self::validRawIntakeData();
        $handoverData = self::validRawHandoverData();

        return [
            'missing identityData censored_bsn' => [
                collect($identityData)->forget('censored_bsn')->toArray(),
                $intakeData,
                $handoverData,
            ],
            'missing intakeData test' => [
                $identityData,
                [],
                $handoverData,
            ],
            'with handoverData, but testMonsterNumber as int' => [
                $identityData,
                $intakeData,
                collect($handoverData)->put('testMonsterNumber', 123)->toArray(),
            ],
            'invalid gender' => [
                array_merge($identityData, ['gender' => 'F']),
                $intakeData,
                $handoverData,
            ],
        ];
    }
}
