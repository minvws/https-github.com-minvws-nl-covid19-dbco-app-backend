<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Organisation;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\BCOPhase;

interface OrganisationRepository
{
    public function getOrganisationByUuid(string $uuid): ?Organisation;

    /**
     * @return Collection<int, EloquentOrganisation>
     */
    public function getAll(): Collection;

    public function getEloquentOrganisationByUuid(string $uuid): ?EloquentOrganisation;

    public function getOrganisationByHpZoneCode(string $hpZoneCode): EloquentOrganisation;

    public function getOrganisationByExternalId(string $externalId): ?EloquentOrganisation;

    public function getOutsourceOrganisations(string $organisationUuid): Collection;

    public function getOutsourceOrganisation(string $organisationUuid, string $outsourcesToOrganisationUuid): ?EloquentOrganisation;

    public function updateOrganisation(EloquentOrganisation $organisation): bool;

    public function updateOrganisationBcoPhase(EloquentOrganisation $organisation, BCOPhase $bcoPhase): bool;

    public function getOrganisationFromEloquentModel(EloquentOrganisation $eloquentOrganisation): Organisation;

    public function listOrganisations(): Collection;

    /**
     * @return Collection<string>
     */
    public function listOrganisationUuids(): Collection;
}
