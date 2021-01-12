<?php

namespace App\Repositories;

use Illuminate\Support\Collection;

interface OrganisationRepository
{
    public function getOrganisationUuidsByExternalIds(array $externalIds): Collection;
}
