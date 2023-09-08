<?php

declare(strict_types=1);

namespace Tests\Unit\Models\CovidCase;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\CovidCase\Vaccination;
use App\Models\Eloquent\EloquentCase;
use App\Models\Shared\VaccineInjection;
use App\Models\Versions\CovidCase\CovidCaseV3;
use App\Schema\Validation\ValidationRule;
use App\Schema\Validation\ValidationRules;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\Vaccine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

use function assert;

class VaccinationTest extends TestCase
{
    use ValidatesModels;

    public function testItCanGetTheMostRecentInjectionFromTheVaccinationFragment(): void
    {
        $case = $this->createCase(YesNoUnknown::yes());

        $latestDate = $this->faker->dateTimeThisYear();

        $case->vaccination->vaccineInjections = [
            VaccineInjection::newInstanceWithVersion(1, function (VaccineInjection $vaccineInjection): void {
                $vaccineInjection->vaccineType = Vaccine::pfizer();
                $vaccineInjection->injectionDate = $this->faker()->dateTime('2021-01-01');
                $vaccineInjection->otherVaccineType = '2';
                $vaccineInjection->isInjectionDateEstimated = true;
            }),
            VaccineInjection::newInstanceWithVersion(1, function (VaccineInjection $vaccineInjection): void {
                $vaccineInjection->vaccineType = Vaccine::pfizer();
                $vaccineInjection->injectionDate = $this->faker()->dateTime('2022-01-01');
                $vaccineInjection->otherVaccineType = '2';
                $vaccineInjection->isInjectionDateEstimated = true;
            }),
            VaccineInjection::newInstanceWithVersion(1, static function (VaccineInjection $vaccineInjection) use ($latestDate): void {
                $vaccineInjection->vaccineType = Vaccine::pfizer();
                $vaccineInjection->injectionDate = $latestDate;
                $vaccineInjection->otherVaccineType = '2';
                $vaccineInjection->isInjectionDateEstimated = true;
            }),
        ];

        $this->assertEquals($latestDate, $case->vaccination->latestInjection()->injectionDate);
    }

    public function testItReturnsNullWhenVaccineInjectionsNull(): void
    {
        $case = $this->createCase(YesNoUnknown::yes());

        $case->vaccination->vaccineInjections = null;

        $this->assertNull($case->vaccination->latestInjection());
    }

    public function testItReturnsNullWhenVaccineInjectionsEmpty(): void
    {
        $case = $this->createCase(YesNoUnknown::yes());

        $case->vaccination->vaccineInjections = [];
        $this->assertNull($case->vaccination->latestInjection());
    }

    public function testItReturnsNullWhenVaccineInjectionsNullButInjectionCountSet(): void
    {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        $case->created_at = $this->faker->dateTimeThisYear();
        assert($case instanceof CovidCaseV3);

        $vaccination = Vaccination::getSchema()->getVersion(3)->newInstance();
        $vaccination->vaccinationCount = $this->faker->numberBetween(1, 9);
        $vaccination->vaccineInjections = [];
        $vaccination->isVaccinated = YesNoUnknown::yes();

        $case->vaccination = $vaccination;

        $this->assertNull($case->vaccination->latestInjection());
    }

    #[DataProvider('validVaccinationV1DataProvider')]
    #[DataProvider('validVaccinationV2DataProvider')]
    #[DataProvider('validVaccinationV3DataProvider')]
    public function testValidVaccinationFragmentValidation(
        array $data,
        array $additionalData,
        string $severityLevel = ValidationRules::WARNING,
    ): void {
        $validatedData = [];

        $additionalData = [];

        $validationResult = $this->validateModel(
            Vaccination::class,
            $data,
            $validatedData,
            $severityLevel,
            $additionalData,
            [ValidationRule::TAG_OSIRIS_FINAL],
        );

        $this->assertEmpty($validationResult);
    }

    #[DataProvider('invalidVaccinationV3DataProvider')]
    public function testInvalidVaccinationFragmentValidation(
        array $data,
        array $additionalData,
        string $severityLevel = ValidationRules::WARNING,
    ): void {
        $validatedData = [];

        $validationResult = $this->validateModel(
            Vaccination::class,
            $data,
            $validatedData,
            $severityLevel,
            $additionalData,
            [ValidationRule::TAG_OSIRIS_FINAL],
        );

        $this->assertArrayHasKey($severityLevel, $validationResult);
    }

    public static function validVaccinationV1DataProvider(): array
    {
        return [
            'v1 empty' => [['schemaVersion' => 1], []],
        ];
    }

    public static function validVaccinationV2DataProvider(): array
    {
        return [
            'v2 empty' => [['schemaVersion' => 2], []],
        ];
    }

    public static function validVaccinationV3DataProvider(): array
    {
        return [
            'v3 empty' => [['schemaVersion' => 3], []],
            'v3 injectionDate after 2021-1-6 and before tomorrow' => [
                ['vaccineInjections' => [['injectionDate' => '2021-1-6']]],
                [],
            ],
            'v3 injectionCount = 0' => [['vaccinationCount' => 0], []],
            'v3 injectionCount = 6' => [['vaccinationCount' => 6], []],
        ];
    }

    public static function invalidVaccinationV3DataProvider(): array
    {
        return [
            'v3 injectionDate before 2021-1-6' => [
                ['vaccineInjections' => [['injectionDate' => '2021-1-1']]],
                [],
            ],
            'v3 injectionDate tomorrow' => [['vaccineInjections' => [['injectionDate' => 'tomorrow']]], []],
            'v3 injectionCount > 6' => [['vaccinationCount' => 7], [], ValidationRules::NOTICE],
        ];
    }

    private function createCase(
        YesNoUnknown $isVaccinated,
        ?Vaccine $vaccine = null,
        ?DateTimeInterface $injectionDate = null,
    ): EloquentCase {
        $case = EloquentCase::getSchema()->getVersion(3)->newInstance();
        assert($case instanceof CovidCaseV3);

        $case->createdAt = CarbonImmutable::now();
        $case->vaccination->isVaccinated = $isVaccinated;

        if ($isVaccinated === YesNoUnknown::yes()) {
            $case->vaccination->vaccineInjections = [
                VaccineInjection::newInstanceWithVersion(
                    1,
                    static function (VaccineInjection $vaccineInjection) use ($vaccine, $injectionDate): void {
                        $vaccineInjection->vaccineType = $vaccine;
                        $vaccineInjection->injectionDate = $injectionDate;
                        $vaccineInjection->otherVaccineType = '2';
                        $vaccineInjection->isInjectionDateEstimated = true;
                    },
                ),
            ];
        }

        return $case;
    }
}
