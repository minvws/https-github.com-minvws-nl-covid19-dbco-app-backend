<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Eloquent\EloquentOrganisation;
use App\Repositories\OrganisationRepository;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\BCOPhase;

class OrganisationService
{
    private OrganisationRepository $organisationRepository;

    public function __construct(
        OrganisationRepository $organisationRepository,
    ) {
        $this->organisationRepository = $organisationRepository;
    }

    public function listOrganisations(): Collection
    {
        return $this->organisationRepository->listOrganisations();
    }

    /**
     * @return Collection<string>
     *
     * @codeCoverageIgnore
     */
    public function listOrganisationUuids(): Collection
    {
        return $this->organisationRepository->listOrganisationUuids();
    }

    public function updateOrganisation(EloquentOrganisation $organisation): bool
    {
        return $this->organisationRepository->updateOrganisation($organisation);
    }

    public function updateOrganisationBcoPhase(EloquentOrganisation $organisation, BCOPhase $bcoPhase): bool
    {
        return $this->organisationRepository->updateOrganisationBcoPhase($organisation, $bcoPhase);
    }
}
