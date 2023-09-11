<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Intake;

use App\Exceptions\IntakeException;
use App\Models\Intake\RawIntake;
use App\Services\Intake\RawIntakeValidator;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Tests\DataProvider\RawIntakeDataProvider;
use Tests\Feature\FeatureTestCase;

use function array_merge;

class RawIntakeValidatorTest extends FeatureTestCase
{
    #[DataProviderExternal(RawIntakeDataProvider::class, 'validRawIntakeDataProvider')]
    public function testValidateRawIntake(array $identityData, array $intakeData, ?array $handoverData): void
    {
        $rawIntake = new RawIntake(
            'intake-id',
            'bco',
            'intake-source',
            $identityData,
            $intakeData,
            $handoverData,
            CarbonImmutable::now(),
        );

        $rawIntakeValidator = $this->app->get(RawIntakeValidator::class);
        $validatedRawIntake = $rawIntakeValidator->validateRawIntake($rawIntake);

        $this->assertEquals($rawIntake, $validatedRawIntake);
    }

    #[DataProvider('mittensGenderDataProvider')]
    public function testValidateRawIntakeMittensGender(string $mittensGender): void
    {
        $rawIntake = new RawIntake(
            'intake-id',
            'bco',
            'intake-source',
            array_merge(RawIntakeDataProvider::validRawIdentityData(), ['gender' => $mittensGender]),
            RawIntakeDataProvider::validRawIntakeData(),
            RawIntakeDataProvider::validRawHandoverData(),
            CarbonImmutable::now(),
        );

        $rawIntakeValidator = $this->app->get(RawIntakeValidator::class);
        $rawIntakeValidator->validateRawIntake($rawIntake);

        $this->assertEquals($rawIntake->getIdentityData()['gender'], $mittensGender);
    }

    public static function mittensGenderDataProvider(): array
    {
        return [
            ['M'],
            ['V'],
            ['O'],
        ];
    }

    #[DataProviderExternal(RawIntakeDataProvider::class, 'invalidRawIntakeDataProvider')]
    public function testValidateRawIntakeFails(array $identityData, array $intakeData, ?array $handoverData): void
    {
        $rawIntake = new RawIntake(
            'intake-id',
            'bco',
            'intake-source',
            $identityData,
            $intakeData,
            $handoverData,
            CarbonImmutable::now(),
        );

        $rawIntakeValidator = $this->app->get(RawIntakeValidator::class);

        $this->expectException(IntakeException::class);
        $this->expectExceptionMessage('Received invalid intake data');
        $rawIntakeValidator->validateRawIntake($rawIntake);
    }
}
