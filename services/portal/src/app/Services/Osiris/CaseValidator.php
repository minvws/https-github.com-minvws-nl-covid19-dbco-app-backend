<?php

declare(strict_types=1);

namespace App\Services\Osiris;

use App\Models\Eloquent\EloquentCase;
use App\Models\Versions\CovidCase\Vaccination\VaccinationV3Up;
use App\Models\Versions\Shared\VaccineInjection\VaccineInjectionCommon;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class CaseValidator
{
    /**
     * @return Collection<string>
     */
    public function validate(EloquentCase $case): Collection
    {
        $errors = new Collection();

        // Melding ontvangen GGD ligt op of voor geboortedatum
        if ($this->isBefore($case->createdAt, $case->index->dateOfBirth)) {
            $errors->push('createdAt_before_dateOfBirth');
        }

        // Opnamedatum ICU ligt voor Datum eerste ziektedag
        if ($this->isBefore($case->hospital->admittedInICUAt, $case->date_of_symptom_onset)) {
            $errors->push('admittedInICUAt_before_dateOfSymptomOnset');
        }

        // Opnamedatum ziekenhuis ligt voor Datum eerste ziektedag
        if ($this->isBefore($case->hospital->admittedAt, $case->date_of_symptom_onset)) {
            $errors->push('admittedAt_before_dateOfSymptomOnset');
        }

        // Datum overlijden ligt voor Datum eerste ziektedag
        if ($this->isBefore($case->deceased->deceasedAt, $case->date_of_symptom_onset)) {
            $errors->push('deceasedAt_before_dateOfSymptomOnset');
        }

        // Datum labuitslag ligt voor Datum eerste ziektedag
        if ($this->isBefore($case->test->dateOfResult, $case->date_of_symptom_onset)) {
            $errors->push('dateOfResult_before_dateOfSymptomOnset');
        }

        // Datum eerste zieketedag ligt voor Geboortedatum
        if ($this->isBefore($case->date_of_symptom_onset, $case->index->dateOfBirth)) {
            $errors->push('dateOfSymptomOnset_before_dateOfBirth');
        }

        // Opnamedatum ligt voor Geboortedatum
        if ($this->isBefore($case->hospital->admittedAt, $case->index->dateOfBirth)) {
            $errors->push('admittedAt_before_dateOfBirth');
        }

        // Datum overlijden ligt voor Opnamedatum ziekenhuis
        if ($this->isBefore($case->deceased->deceasedAt, $case->hospital->admittedAt)) {
            $errors->push('deceasedAt_before_hospitalAdmittedAt');
        }

        // Datum overlijden ligt voor Opnamedatum ICU
        if ($this->isBefore($case->deceased->deceasedAt, $case->hospital->admittedInICUAt)) {
            $errors->push('deceasedAt_before_admittedInICUAt');
        }

        // Datum overlijden ligt voor startdatum Surveillance (1 maart 2020)
        if ($this->isBefore($case->deceased->deceasedAt, CarbonImmutable::createStrict(2020, 3, 1))) {
            $errors->push('deceasedAt_before_20200301');
        }

        // Melding ontvangen GGD is eerder dan eerst gemelde covid case (27 februari 2020)
        if ($this->isBefore($case->createdAt, CarbonImmutable::createStrict(2020, 2, 27))) {
            $errors->push('createdAt_before_20200227');
        }

        // Opnamedatum ziekenhuis ligt voor startdatum Surveillance (1 maart 2020)
        if ($this->isBefore($case->hospital->admittedAt, CarbonImmutable::createStrict(2020, 3, 1))) {
            $errors->push('admittedAt_before_20200301');
        }

        // Datum labuitslag ligt voor startdatum Surveillance (1 maart 2020)
        if ($this->isBefore($case->test->dateOfResult, CarbonImmutable::createStrict(2020, 3, 1))) {
            $errors->push('dateOfResult_before_20200301');
        }

        // Geboortedatum ligt voor 1 januari 1906, oudste persoon in Nederland is geboren in 1906
        if ($this->isBefore($case->index->dateOfBirth, CarbonImmutable::createStrict(1906, 1, 1))) {
            $errors->push('dateOfBirth_before_19060101');
        }

        // 1e vacdatum ligt niet tussen 6 jan 2021 en datum invoer (huidige dag)
        if (
            $case->vaccination instanceof VaccinationV3Up
            && $case->vaccination->vaccinationCount === 1
        ) {
            /** @var VaccineInjectionCommon $vaccineInjection */
            $vaccineInjection = $case->vaccination->vaccineInjections()->sortBy('injectionDate')->first();
            if (
                $vaccineInjection !== null
                && $this->isBefore($vaccineInjection->injectionDate, CarbonImmutable::createStrict(2021, 1, 6))
            ) {
                $errors->push('firstVaccineInjection_before_20210106');
            }
        }

        // 2e vacdatum ligt niet tussen 27 jan 2021 en datum invoer (huidige dag)
        if (
            $case->vaccination instanceof VaccinationV3Up
            && $case->vaccination->vaccinationCount === 2
        ) {
            /** @var VaccineInjectionCommon $vaccineInjection */
            $vaccineInjection = $case->vaccination->vaccineInjections()->sortBy('injectionDate')->last();
            if (
                $vaccineInjection !== null
                && $this->isBefore($vaccineInjection->injectionDate, CarbonImmutable::createStrict(2021, 1, 27))
            ) {
                $errors->push('lastVaccineInjection_before_20210127');
            }
        }

        // Laatste vacdatum ligt niet tussen 6 jan 2021 en datum invoer (huidige dag)
        if (
            $case->vaccination instanceof VaccinationV3Up
            && $case->vaccination->vaccinationCount > 0
        ) {
            /** @var VaccineInjectionCommon $vaccineInjection */
            $vaccineInjection = $case->vaccination->vaccineInjections()->sortBy('injectionDate')->last();
            if (
                $vaccineInjection !== null
                && $this->isBefore($vaccineInjection->injectionDate, CarbonImmutable::createStrict(2021, 1, 6))
            ) {
                $errors->push('lastVaccineInjection_before_20210106');
            }
        }

        // Eerste ziektedag van laatste covid periode ligt niet tussen 1 januari 2020 en melddatum GGD huidige melding
        if (
            $case->test->isReinfection === YesNoUnknown::yes()
            && ($this->isBefore($case->test->previousInfectionDateOfSymptom, CarbonImmutable::createStrict(2020, 1, 1))
                || $this->isAfter($case->test->previousInfectionDateOfSymptom, $case->createdAt)
            )
        ) {
            $errors->push('previousInfectionDateOfSymptom_before_20200101_or_after_createdAt');
        }

        return $errors;
    }

    private function isAfter(?DateTimeInterface $first, ?DateTimeInterface $second): bool
    {
        if ($first === null || $second === null) {
            return false;
        }

        return CarbonImmutable::instance($first)->isAfter(CarbonImmutable::instance($second));
    }

    private function isBefore(?DateTimeInterface $first, ?DateTimeInterface $second): bool
    {
        if ($first === null || $second === null) {
            return false;
        }

        return CarbonImmutable::instance($first)->isBefore(CarbonImmutable::instance($second));
    }
}
