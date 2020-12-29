<?php

namespace App\Repositories;

use App\Models\BCOUser;
use App\Models\Eloquent\EloquentCase;
use App\Models\CovidCase;
use DateTimeImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $case = null;
        $dbCase = $this->getCaseFromDb($caseUuid);
        if ($dbCase != null) {
            $case = $this->caseFromEloquentModel($dbCase);
            $this->verifyExportables($case);
        }
        return $case;
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
        })->where(function($query) {
            $query->whereNull('copied_at')->orWhereRaw('copied_at < updated_at');
        })->get();

        $case->hasExportables = ($exportableTasks->count() > 0);
    }

    /**
     * Returns all the cases of a specicic user
     * @return LengthAwarePaginator
     */
    public function getCasesByAssignedUser(BCOUser $user): LengthAwarePaginator
    {
        $paginator = EloquentCase::where('assigned_uuid', $user->uuid)
            ->select('covidcase.*', 'bcouser.name as assigned_name')
            ->join('bcouser', 'bcouser.uuid', '=', 'covidcase.assigned_uuid')
            ->orderBy('covidcase.updated_at', 'desc')->paginate(config('view.rowsPerPage'));

        $cases = array();

        foreach($paginator->items() as $dbCase) {
            $case = $this->caseFromEloquentModel($dbCase);
            $this->verifyExportables($case);
            $cases[] = $case;
        };

        $paginator->setCollection(collect($cases));
        return $paginator;
    }

    public function getCasesByOrganisation(BCOUser $user): LengthAwarePaginator
    {
        $paginator = EloquentCase::where('user_organisation.user_uuid', $user->uuid)
                ->select('covidcase.*', 'bcouser.name as assigned_name')
                ->join('user_organisation', 'user_organisation.organisation_uuid', '=', 'covidcase.organisation_uuid')
                ->join('bcouser', 'bcouser.uuid', '=', 'covidcase.assigned_uuid')
                ->orderBy('covidcase.updated_at', 'desc')->paginate(config('view.rowsPerPage'));

        $cases = array();

        foreach($paginator->items() as $dbCase) {
            $case = $this->caseFromEloquentModel($dbCase);
            $this->verifyExportables($case);
            $cases[] = $case;
        };

        $paginator->setCollection(collect($cases));

        return $paginator;
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
     * @return bool True if succesful
     */
    public function updateCase(CovidCase $case): bool
    {
        // TODO fixme: this retrieves the object from the db, again; but eloquent won't let us easily instantiate
        // an object directly from a CovidCase.
        $dbCase = $this->getCaseFromDb($case->uuid);
        $dbCase->case_id = $case->caseId;
        $dbCase->assigned_uuid = $case->assignedUuid;
        $dbCase->name = $case->name;
        $dbCase->status = $case->status;
        $dbCase->copied_at = $case->copiedAt != null ? $case->copiedAt->toDateTimeImmutable() : null;
        $dbCase->exported_at = $case->exportedAt != null ? $case->exportedAt->toDateTimeImmutable() : null;
        $dbCase->export_id = $case->exportId;
        $dbCase->date_of_symptom_onset = $case->dateOfSymptomOnset != null ? $case->dateOfSymptomOnset->toDateTimeImmutable() : null;
        return $dbCase->save();
    }

    /**
     * @param CovidCase $case
     * @param DateTimeImmutable $windowExpiresAt
     * @param DateTimeImmutable $pairingExpiresAt
     * @return mixed
     */
    public function setExpiry(CovidCase $case, DateTimeImmutable $windowExpiresAt, DateTimeImmutable $pairingExpiresAt)
    {
        $dbCase = $this->getCaseFromDb($case->uuid);
        $dbCase->window_expires_at = $windowExpiresAt;
        $dbCase->pairing_expires_at = $pairingExpiresAt;
        $dbCase->save();
    }

    private function caseFromEloquentModel(EloquentCase $dbCase): CovidCase
    {
        $case = new CovidCase();
        $case->uuid = $dbCase->uuid;
        $case->caseId = $dbCase->case_id;
        $case->organisationUuid = $dbCase->organisation_uuid;
        $case->dateOfSymptomOnset = $dbCase->date_of_symptom_onset != NULL ? new Date($dbCase->date_of_symptom_onset) : null;
        $case->name = $dbCase->name;
        $case->owner = $dbCase->owner;
        $case->status = $dbCase->status;
        $case->assignedUuid = $dbCase->assigned_uuid;
        $case->updatedAt = new Date($dbCase->updated_at);
        $case->createdAt = new Date($dbCase->created_at);
        $case->copiedAt = $dbCase->copied_at != null ? new Date($dbCase->copied_at) : null;
        $case->exportedAt = $dbCase->exported_at != null ? new Date($dbCase->exported_at) : null;
        $case->exportId = $dbCase->export_id;
        $case->pairingExpiresAt = $dbCase->pairing_expires_at != null ? new Date($dbCase->pairing_expires_at) : null;
        $case->windowExpiresAt = $dbCase->window_expires_at != null ? new Date($dbCase->window_expires_at) : null;
        $case->indexSubmittedAt = $dbCase->index_submitted_at != null ? new Date($dbCase->index_submitted_at) : null;

        if ($case->assignedUuid !== null) {
            $case->assignedName = $dbCase->assigned_name;
        }

        return $case;
    }

}
