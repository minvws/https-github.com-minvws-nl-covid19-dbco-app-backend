<?php

namespace App\Repositories;

use App\Models\Eloquent\EloquentOrganisation;
use Illuminate\Support\Collection;

class DbOrganisationRepository implements OrganisationRepository
{
    public function getOrganisationUuidsByExternalIds(array $externalIds): Collection
    {
        $orgIds = [];
        $dbOrgs = EloquentOrganisation::whereIn('external_id', $externalIds)->get()->all();
        foreach($dbOrgs as $dbOrg) {
            $orgIds[] = $dbOrg->uuid;
        }
        return collect($orgIds);
    }
}
