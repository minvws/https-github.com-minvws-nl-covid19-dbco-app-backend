<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use MinVWS\DBCO\Enum\Models\Country;
use MinVWS\DBCO\Enum\Models\Gender;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\JobSector;
use MinVWS\DBCO\Enum\Models\Symptom;
use MinVWS\DBCO\Enum\Models\UnderlyingSuffering;
use MinVWS\DBCO\Enum\Models\Vaccine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function collect;
use function rand;

class IntakeFragmentDataFaker extends AbstractFragmentDataFaker
{
    public static function createRandomUnderlyingSufferingValues(): array
    {
        $underlyingSufferingItems = collect(UnderlyingSuffering::all());
        $underlyingSuffering = [];
        $underlyingSufferingCount = rand(0, 3);
        for ($i = 0; $i < $underlyingSufferingCount; $i++) {
            $underlyingSuffering[] = $underlyingSufferingItems->random();
        }
        return $underlyingSuffering;
    }

    public static function createRandomTestData(): array
    {
        $dateOfSymptomOnset = CarbonImmutable::yesterday();
        $dateOfTest = CarbonImmutable::yesterday();
        $previousInfectionDateOfSymptom = $dateOfTest->clone()->subMonths(3);

        $infectionIndicator = collect(InfectionIndicator::all());
        return [
            'dateOfSymptomOnset' => $dateOfSymptomOnset->format('Y-m-d'),
            'dateOfTest' => $dateOfTest->format('Y-m-d'),
            'infectionIndicator' => $infectionIndicator->random()->value,
            'isReinfection' => YesNoUnknown::no(),
            'previousInfectionDateOfSymptom' => $previousInfectionDateOfSymptom->format('Y-m-d'),
        ];
    }

    public static function createRandomIndexData(): array
    {
        $allGenders = collect(Gender::all());
        $dateOfBirth = CarbonImmutable::now()->subYears(rand(12, 99))->subMonths(rand(0, 12))->subDays(rand(0, 31));
        return [
            'gender' => $allGenders->random(),
            'dateOfBirth' => $dateOfBirth,
        ];
    }

    public static function createRandomAbroadData(): array
    {
        return [
            'wasAbroad' => YesNoUnknown::yes(),
            'trips' => static::createRandomAbroadTripsValues(rand(0, 3)),
        ];
    }

    public static function createRandomAbroadTripsValues(int $tripsCount): array
    {
        $trips = [];
        for ($i = 0; $i < $tripsCount; $i++) {
            $countries = static::getFaker()->randomElements(Country::all(), rand(0, 3));
            $departureDate = static::getFaker()->dateTimeBetween('-14 days', '-2 days');
            $returnDate = static::getFaker()->dateTimeBetween($departureDate);
            $trips[] = [
                'departureDate' => $departureDate->format('Y-m-d'),
                'returnDate' => $returnDate->format('Y-m-d'),
                'countries' => $countries,
            ];
        }
        return $trips;
    }

    public static function createRandomVaccinationData(): array
    {
        if (rand(0, 1)) {
            return [
                'isVaccinated' => YesNoUnknown::no()->value,
                'vaccineInjections' => [],
            ];
        }

        $vaccinations = [
            'isVaccinated' => YesNoUnknown::yes()->value,
        ];
        $vaccinationCount = rand(0, 3);

        $vaccines = collect(Vaccine::all());
        for ($i = 0; $i < $vaccinationCount; $i++) {
            $injectionDate = new CarbonImmutable(static::getFaker()->dateTimeBetween('-4 months'));
            $vaccinations['vaccineInjections'][] = [
                'injectionDate' => $injectionDate->format('Y-m-d'),
                'vaccineType' => $vaccines->random()->value,
            ];
        }
        return $vaccinations;
    }

    public static function createRandomContactsData(): array
    {
        return [
            [
                'general' => [
                    'isSource' => true,
                    'reference' => '1234567',
                ],
            ],
        ];
    }

    public static function createRandomSymptomsData(): array
    {
        return [
            'hasSymptoms' => YesNoUnknown::yes(),
            'symptoms' => static::createRandomSymptomsValues(),
        ];
    }

    private static function createRandomSymptomsValues(): array
    {
        $allSymptoms = collect(Symptom::all());
        $randomSymptoms = [];
        $randomSymptomsCount = rand(0, 3);
        for ($i = 0; $i < $randomSymptomsCount; $i++) {
            $randomSymptoms[] = $allSymptoms->random()->value;
        }
        return $randomSymptoms;
    }

    public static function createRandomUnderlyingSufferingData(): array
    {
        return [
            'hasUnderlyingSuffering' => YesNoUnknown::yes(),
            'items' => static::createRandomUnderlyingSufferingValues(),
        ];
    }

    public static function createRandomSourceEnvironmentsData(): array
    {
        return [
            'hasLikelySourceEnvironments' => YesNoUnknown::yes(),
            'likelySourceEnvironments' => static::createRandomLikelySourceEnvironmentsValues(),
        ];
    }

    private static function createRandomLikelySourceEnvironmentsValues(): array
    {
        return [
            ContextCategory::accomodatieBinnenland(),
            ContextCategory::bezoek(),
        ];
    }

    public static function createRandomJobData(): array
    {
        return [
            'wasAtJob' => static::getFaker()->randomElement(YesNoUnknown::all()),
            'sectors' => static::getFaker()->randomElements(JobSector::all(), rand(0, 2)),
        ];
    }
}
