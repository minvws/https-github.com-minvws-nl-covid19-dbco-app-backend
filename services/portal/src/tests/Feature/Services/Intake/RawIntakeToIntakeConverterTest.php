<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Intake;

use App\Exceptions\IntakeException;
use App\Models\Intake\RawIntake;
use App\Services\Intake\RawIntakeToIntakeConverter;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\IntakeType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProvider\RawIntakeDataProvider;
use Tests\Feature\FeatureTestCase;

use function config;

#[Group('intake')]
class RawIntakeToIntakeConverterTest extends FeatureTestCase
{
    #[DataProviderExternal(RawIntakeDataProvider::class, 'validRawIntakeDataProvider')]
    public function testConvert(array $identityData, array $intakeData, ?array $handoverData): void
    {
        CarbonImmutable::setTestNow('2020-01-01');

        // overwrite ggd_region in intakedata, and make sure forcing is off
        $organisation = $this->createOrganisation();
        $identityData['ggd_region'] = $organisation->external_id;
        config()->set('misc.intake.force_region_code', false);

        $rawIntake = new RawIntake(
            'intake-id',
            'bco',
            'intake-source',
            $identityData,
            $intakeData,
            $handoverData,
            CarbonImmutable::now(),
        );

        $rawIntakeToIntakeConverter = $this->app->get(RawIntakeToIntakeConverter::class);
        $intake = $rawIntakeToIntakeConverter->convert($rawIntake);

        $this->assertEquals('intake-id', $intake->uuid);
        $this->assertEquals(IntakeType::bco(), $intake->type);
        $this->assertEquals($organisation->uuid, $intake->organisation_uuid);
        $this->assertEquals($identityData['guid'], $intake->pseudo_bsn_guid);
        $this->assertTrue(CarbonImmutable::now()->equalTo($intake->received_at));
        $this->assertEquals('1990-09-25', $intake->date_of_birth->format('Y-m-d'));

        if ($handoverData !== null) {
            $this->assertEquals('testMonsterNumber', $intake->identifier_type);
        } else {
            $this->assertEquals('pseudoBsnGuid', $intake->identifier_type);
            $this->assertEquals('d7be5717-5f5b-4e1a-9d2e-fba43bd916f0', $intake->identifier);
        }
    }

    #[DataProviderExternal(RawIntakeDataProvider::class, 'validRawIntakeDataProvider')]
    public function testConvertWithInvalidIntakeTypeFails(
        array $identityData,
        array $intakeData,
        ?array $handoverData,
    ): void {
        $rawIntake = new RawIntake(
            'intake-id',
            'invalid',
            'intake-source',
            $identityData,
            $intakeData,
            $handoverData,
            CarbonImmutable::now(),
        );

        $rawIntakeToIntakeConverter = $this->app->get(RawIntakeToIntakeConverter::class);

        $this->expectException(IntakeException::class);
        $rawIntakeToIntakeConverter->convert($rawIntake);
    }

    #[DataProvider('alternativeDateOfBirthDataProvider')]
    public function testConvertWithAlternativeDateOfBirth(string $intakeDateOfBirth, string $expectedDateOfBirth): void
    {
        $organisation = $this->createOrganisation();
        config()->set('misc.intake.force_region_code', $organisation->external_id);

        // overwrite the dateOfBirth
        $identityData = RawIntakeDataProvider::validRawIdentityData();
        $identityData['birth_date'] = $intakeDateOfBirth;

        $rawIntake = new RawIntake(
            'intake-id',
            'bco',
            'intake-source',
            $identityData,
            RawIntakeDataProvider::validRawIntakeData(),
            RawIntakeDataProvider::validRawHandoverData(),
            CarbonImmutable::now(),
        );

        $rawIntakeToIntakeConverter = $this->app->get(RawIntakeToIntakeConverter::class);
        $intake = $rawIntakeToIntakeConverter->convert($rawIntake);

        $this->assertEquals($expectedDateOfBirth, $intake->date_of_birth->format('Y-m-d'));
    }

    public static function alternativeDateOfBirthDataProvider(): array
    {
        return [
            'default' => ['2001-11-11', '2001-11-11'],
            'unknown day' => ['2001-11-X', '2001-01-01'],
            'unknown month and day' => ['2001-X-X', '2001-01-01'],
            'no month and day' => ['2001', '2001-01-01'],
        ];
    }

    public function testConvertWithUnknownDateOfBirthFails(): void
    {
        $organisation = $this->createOrganisation();
        config()->set('misc.intake.force_region_code', $organisation->external_id);

        // overwrite the dateOfBirth
        $identityData = RawIntakeDataProvider::validRawIdentityData();
        $identityData['birth_date'] = 'unknown';

        $rawIntake = new RawIntake(
            'intake-id',
            'bco',
            'intake-source',
            $identityData,
            RawIntakeDataProvider::validRawIntakeData(),
            RawIntakeDataProvider::validRawHandoverData(),
            CarbonImmutable::now(),
        );

        $rawIntakeToIntakeConverter = $this->app->get(RawIntakeToIntakeConverter::class);

        $this->expectException(IntakeException::class);
        $rawIntakeToIntakeConverter->convert($rawIntake);
    }

    public function testConvertWithForcedRegionCode(): void
    {
        $rawIntake = new RawIntake(
            'id',
            'bco',
            'source',
            RawIntakeDataProvider::validRawIdentityData(),
            RawIntakeDataProvider::validRawIntakeData(),
            RawIntakeDataProvider::validRawHandoverData(),
            CarbonImmutable::now(),
        );

        $organisation = $this->createOrganisation();
        config()->set('misc.intake.force_region_code', $organisation->external_id);

        $rawIntakeToIntakeConverter = $this->app->get(RawIntakeToIntakeConverter::class);
        $intake = $rawIntakeToIntakeConverter->convert($rawIntake);

        $this->assertEquals($organisation->uuid, $intake->organisation_uuid);
    }

    public function testConvertWithUnknownRegionCode(): void
    {
        $rawIntake = new RawIntake(
            'id',
            'bco',
            'source',
            RawIntakeDataProvider::validRawIdentityData(),
            RawIntakeDataProvider::validRawIntakeData(),
            RawIntakeDataProvider::validRawHandoverData(),
            CarbonImmutable::now(),
        );

        config()->set('misc.intake.force_region_code', 'unknown');

        $rawIntakeToIntakeConverter = $this->app->get(RawIntakeToIntakeConverter::class);

        $this->expectException(IntakeException::class);

        $rawIntakeToIntakeConverter->convert($rawIntake);
    }
}
