<?php

namespace App\Repositories;

use App\Models\BCOUser;
use App\Models\Eloquent\EloquentCase;
use App\Models\CovidCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;

class DbCaseRepository implements CaseRepository
{
    /**
     * Returns the case and its task list.
     *
     * @param string $caseUuid Case identifier.
     *
     * @return CovidCase The found case (or null if not found)
     */
    public function getCase(string $caseUuid): ?CovidCase
    {
        $dbCase = $this->getCaseFromDb($caseUuid);
        return $dbCase != null ? $this->caseFromEloquentModel($dbCase): null;
    }

    /**
     * @param string $caseUuid
     * @return EloquentCase|null
     */
    private function getCaseFromDb(string $caseUuid): ?EloquentCase
    {
        $cases = EloquentCase::where('uuid', $caseUuid)->get();
        return $cases->first();
    }

    /**
     * Update a case with info about whether there is data to export to HPZone
     * TODO: It may be more efficient to join/subquery this when retrieving the cases, instead
     * of doing it per row.
     * @param CovidCase $case
     */
    private function verifyExportables(CovidCase $case)
    {
        $exportableTasks = DB::table('task')->where('case_uuid', '=', $case->uuid)
        ->whereNotNull('questionnaire_uuid')
        ->where(function($query) {
            $query->whereNull('exported_at')->orWhereRaw('exported_at < updated_at');
        })->get();

        $case->hasExportables = ($exportableTasks->count() > 0);
    }

    /**
     * Returns all the cases of a specicic user
     * @return Collection
     */
    public function getCasesByAssignedUser(BCOUser $user): Collection
    {
        $dbCases = EloquentCase::where('assigned_uuid', $user->uuid)->orderBy('covidcase.updated_at', 'desc')->get();

        $cases = array();

        foreach($dbCases as $dbCase) {
            $case = $this->caseFromEloquentModel($dbCase);
            $this->verifyExportables($case);
            $cases[] = $case;
        };

        return collect($cases);
    }

    public function getCasesByOrganisation(BCOUser $user): Collection
    {
        $dbCases = EloquentCase::where('user_organisation.user_uuid', $user->uuid)
                ->select('covidcase.*')
                ->join('user_organisation', 'user_organisation.organisation_uuid', '=', 'covidcase.organisation_uuid')
                ->orderBy('covidcase.updated_at', 'desc')->get();

        $cases = array();

        foreach($dbCases as $dbCase) {
            $case = $this->caseFromEloquentModel($dbCase);
            $this->verifyExportables($case);
            $cases[] = $case;
        };

        return collect($cases);
    }

    /**
     * Create a new, empty case.
     *
     * @return CovidCase
     */
    public function createCase(BCOUser $owner, string $initialStatus, ?BCOUser $assignedTo=null): CovidCase
    {
        $dbCase = new EloquentCase();

        $dbCase->owner = $owner->uuid;
        $dbCase->status = $initialStatus;
        $dbCase->organisation_uuid = $owner->organisations[0]->uuid; // TODO fix me: what if user has 2 orgs?

        if ($assignedTo != null) {
            $dbCase->assigned_uuid = $assignedTo->uuid;
        }

        $dbCase->save();
        return $this->caseFromEloquentModel($dbCase);
    }

    /**
     * Update case.
     *
     * @param CovidCase $case Case entity
     */
    public function updateCase(CovidCase $case)
    {
        // TODO fixme: this retrieves the object from the db, again; but eloquent won't let us easily instantiate
        // an object directly from a CovidCase.
        $dbCase = $this->getCaseFromDb($case->uuid);
        $dbCase->case_id = $case->caseId;
        $dbCase->name = $case->name;
        $dbCase->status = $case->status;
        $dbCase->date_of_symptom_onset = $case->dateOfSymptomOnset != null ? $case->dateOfSymptomOnset->toDateTimeImmutable() : null;
        $dbCase->save();
    }

    /**
     * @param CovidCase $case
     * @param Date $windowExpiresAt
     * @param Date $pairingExpiresAt
     * @return mixed
     */
    public function setExpiry(CovidCase $case, Date $windowExpiresAt, Date $pairingExpiresAt)
    {
        $dbCase = $this->getCaseFromDb($case->uuid);
        $dbCase->window_expires_at = $windowExpiresAt->toDateTimeImmutable();
        $dbCase->pairing_expires_at = $pairingExpiresAt->toDateTimeImmutable();
        $dbCase->save();
    }

    private function caseFromEloquentModel(EloquentCase $dbCase): CovidCase
    {
        $case = new CovidCase();
        $case->uuid = $dbCase->uuid;
        $case->caseId = $dbCase->case_id;
        $case->dateOfSymptomOnset = $dbCase->date_of_symptom_onset != NULL ? new Date($dbCase->date_of_symptom_onset) : null;
        $case->name = $dbCase->name;
        $case->owner = $dbCase->owner;
        $case->status = $dbCase->status;
        $case->updatedAt = new Date($dbCase->updated_at);
        $case->pairingExpiresAt = $dbCase->pairing_expires_at != null ? new Date($dbCase->pairing_expires_at) : null;
        $case->windowExpiresAt = $dbCase->window_expires_at != null ? new Date($dbCase->window_expires_at) : null;
        $case->indexSubmittedAt = $dbCase->index_submitted_at != null ? new Date($dbCase->index_submitted_at) : null;
        return $case;
    }

}
