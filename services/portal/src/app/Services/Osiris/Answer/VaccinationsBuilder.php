<?php

declare(strict_types=1);

namespace App\Services\Osiris\Answer;

use App\Models\CovidCase\Vaccination;
use App\Models\Eloquent\EloquentCase;
use App\Models\Shared\VaccineInjection;
use App\Models\Versions\CovidCase\Vaccination\VaccinationV1UpTo2;
use App\Models\Versions\CovidCase\Vaccination\VaccinationV3Up;
use MinVWS\DBCO\Enum\Models\Vaccine;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;
use function in_array;

class VaccinationsBuilder implements Builder
{
    private const MAX_INJECTIONS = 4;
    private const VALID_TYPES = [
        1 => ['1', '2', '3', '4', '7', '8'],
        2 => ['1', '2', '3', '7', '8'],
        3 => ['1', '2', '7', '8'],
        4 => ['1', '2', '7', '8'],
    ];

    public function __construct()
    {
    }

    public function build(EloquentCase $case): array
    {
        if (
            !isset($case->vaccination->isVaccinated) ||
            !$case->vaccination instanceof Vaccination ||
            $case->vaccination->isVaccinated !== YesNoUnknown::yes()
        ) {
            return [];
        }

        $vaccination = $case->vaccination;
        assert($vaccination instanceof VaccinationV1UpTo2 || $vaccination instanceof VaccinationV3Up);

        return Utils::collectAnswers(
            $vaccination->vaccineInjections,
            self::MAX_INJECTIONS,
            fn (VaccineInjection $injection, int $index) => $this->buildInjectionAnswers($injection, $index + 1)
        );
    }

    private function buildInjectionAnswers(VaccineInjection $injection, int $index): array
    {
        $answers = [];

        $answers[] = $this->buildInjectionTypeAnswer($injection->vaccineType, $index);

        if ($injection->vaccineType === Vaccine::other() && $injection->otherVaccineType !== null) {
            $answers[] = new Answer("NCOVvacmerk{$index}and", $injection->otherVaccineType);
        }

        if ($injection->injectionDate !== null) {
            $answers[] = new Answer("NCOVpatvac{$index}Dt", Utils::formatDate($injection->injectionDate));
        }

        if ($index >= 3 && $index <= 4) {
            $answers[] = new Answer("NCOVafweervac{$index}", 'Onb');
        }

        return $answers;
    }

    private function buildInjectionTypeAnswer(?Vaccine $vaccine, int $index): Answer
    {
        $type = match ($vaccine) {
            Vaccine::pfizer() => '1',
            Vaccine::moderna() => '2',
            Vaccine::astrazeneca() => '3',
            Vaccine::janssen() => '4',
            Vaccine::gsk() => '5',
            Vaccine::curevac() => '6',
            Vaccine::other() => '7',
            default => '8' // unknown
        };

        if (!in_array($type, self::VALID_TYPES[$index], true)) {
            $type = '7';
        }

        return new Answer("NCOVvacmerk{$index}", $type);
    }
}
