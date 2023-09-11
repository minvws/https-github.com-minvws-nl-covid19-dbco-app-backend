<?php

declare(strict_types=1);

namespace App\Services\TestResult\Factories\Models\CovidCase;

use App\Dto\TestResultReport\TestResultReport;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Versions\CovidCase\CovidCaseV8;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Services\TestResult\Factories\Enums\TestResultSourceFactory;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\AutomaticAddressVerificationStatus;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;

/**
 * NOTE:
 * We are currently using fixed versions for both the EloquentCase and it's fragments. This is due to possible breakage
 * when one or more of the the fields change type, for example field type changes from string to int. We should only
 * use the "latest" version when we have a solution for the above problem. Until that time there is a test that will
 * fail whenever CovidCaseFactory does not produce a case using the latest versions.
 */
final class CovidCaseFactory
{
    public static function create(
        TestResultReport $testResultReport,
        EloquentOrganisation $organisation,
        ?PseudoBsn $pseudoBsn,
    ): CovidCaseV8 {
        /** @var CovidCaseV8 $covidCase */
        $covidCase = EloquentCase::getSchema()->getVersion(8)->newInstance();
        $covidCase->createdAt = CarbonImmutable::now();
        $covidCase->date_of_test = CarbonImmutable::parse($testResultReport->test->sampleDate->format('Y-m-d'));

        if ($testResultReport->triage->dateOfFirstSymptom instanceof DateTimeInterface) {
            $covidCase->date_of_symptom_onset = $testResultReport->triage->dateOfFirstSymptom;
        }

        $covidCase->source = TestResultSourceFactory::fromSource($testResultReport->test->source)->value;
        $covidCase->organisation()->associate($organisation);
        $covidCase->organisation_uuid = $organisation->uuid;
        $covidCase->bco_phase = $organisation->bco_phase;
        $covidCase->contact = ContactFactory::create($testResultReport->person);
        $covidCase->index = IndexFactory::create($testResultReport->person, $pseudoBsn);
        $covidCase->symptoms = SymptomsFactory::create($testResultReport->triage);
        $covidCase->pseudo_bsn_guid = $pseudoBsn?->getGuid();
        $covidCase->test_monster_number = $testResultReport->orderId;
        $covidCase->general = GeneralFactory::create($testResultReport, $organisation);
        $covidCase->test = TestFactory::create($testResultReport);
        $covidCase->automatic_address_verification_status = AutomaticAddressVerificationStatus::unverified();
        $covidCase->status_index_contact_tracing = ContactTracingStatus::new();

        return $covidCase;
    }
}
