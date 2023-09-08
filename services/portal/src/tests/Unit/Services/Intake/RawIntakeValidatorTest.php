<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Intake;

use App\Exceptions\IntakeException;
use App\Models\Intake\RawIntake;
use App\Services\Intake\RawIntakeValidator;
use Closure;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\DataProvider\RawIntakeDataProvider;
use Tests\TestCase;

use function app;
use function array_filter;
use function array_merge;

#[Group('intake')]
#[Group('intake-validation')]
class RawIntakeValidatorTest extends TestCase
{
    private const REMOVE = '__REMOVE__';

    private function merge(array $base, array $override): array
    {
        $result = array_merge($base, $override);
        return array_filter($result, static fn ($v) => $v !== self::REMOVE);
    }

    public function testMerge(): void
    {
        $this->assertEquals(
            ['a' => 1, 'b' => 2, 'c' => 3],
            $this->merge(['a' => 1, 'b' => 2, 'c' => 3], []),
        );

        $this->assertEquals(
            ['a' => 1, 'b' => 2, 'c' => 3],
            $this->merge(['a' => 1, 'b' => 2], ['c' => 3]),
        );

        $this->assertEquals(
            ['a' => 1, 'b' => 4, 'c' => 3],
            $this->merge(['a' => 1, 'b' => 2], ['b' => 4, 'c' => 3]),
        );

        $this->assertEquals(
            ['a' => 1, 'c' => 4],
            $this->merge(['a' => 1, 'b' => 2, 'c' => 3], ['b' => self::REMOVE, 'c' => 4]),
        );
    }

    public static function validationProvider(): Generator
    {
        yield 'all intake fields filled with valid data should succeed' => [
            [],
            [],
            [],
            true,
        ];

        yield 'some intake data missing should succeed' => [
            [],
            [
                'sourceEnvironments' => self::REMOVE,
            ],
            [],
            true,
        ];

        yield 'invalid identity data should fail' => [
            [
                'first_name' => self::REMOVE,
            ],
            [],
            [],
            false,
        ];

        yield 'invalid intake data should fail' => [
            [],
            [
                'symptoms' => [
                    'hasSymptoms' => 'yes',
                    'symptoms' => [
                        'does_not_exist',
                    ],
                ],
            ],
            [],
            false,
        ];

        yield 'invalid handover data should fail' => [
            [],
            [],
            [
                'testMonsterNumber' => 12_345,
            ],
            false,
        ];

        yield 'unvalidated keys should succeed, but extra keys should be removed' => [
            [],
            [
                'invalidFragment' => [
                    'fieldThatDoesNotExist' => 'yes',
                ],
                'symptoms' => [
                    'hasSymptoms' => 'yes',
                    'symptoms' => [],
                    'fieldThatDoesNotExist' => 'yes',
                ],
            ],
            [],
            true,
            static function (RawIntake $validatedIntake): void {
                self::assertArrayNotHasKey('invalidFragment', $validatedIntake->getIntakeData());
                self::assertArrayHasKey('hasSymptoms', $validatedIntake->getIntakeData()['symptoms']);
                self::assertArrayNotHasKey('fieldThatDoesNotExist', $validatedIntake->getIntakeData()['symptoms']);
            },
        ];
    }

    #[DataProvider('validationProvider')]
    public function testValidation(
        array $identityData,
        array $intakeData,
        array $handoverData,
        bool $expectedResult,
        ?Closure $validResultCallback = null,
    ): void {
        $intake = new RawIntake(
            Uuid::uuid4()->toString(),
            'intake',
            'test',
            $this->merge(RawIntakeDataProvider::validRawIdentityData(), $identityData),
            $this->merge(RawIntakeDataProvider::validRawIntakeData(), $intakeData),
            $this->merge(RawIntakeDataProvider::validRawHandoverData(), $handoverData),
            new DateTimeImmutable(),
        );

        $validator = app()->get(RawIntakeValidator::class);

        if (!$expectedResult) {
            $this->expectException(IntakeException::class);
        }

        $validatedIntake = $validator->validateRawIntake($intake);

        if (!$expectedResult) {
            return;
        }

        $this->assertNotNull($validatedIntake);

        if ($validResultCallback !== null) {
            $validResultCallback($validatedIntake);
        }
    }
}
