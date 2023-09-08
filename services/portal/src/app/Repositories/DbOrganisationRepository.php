<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\OrganisationNotFoundException;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Organisation;
use App\Models\OrganisationType;
use App\Scopes\OrganisationAuthScope;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\BCOPhase;

class DbOrganisationRepository implements OrganisationRepository
{
    public function getOrganisationByUuid(string $uuid): ?Organisation
    {
        $organisation = EloquentOrganisation::where('uuid', $uuid)->first();

        if (!$organisation instanceof EloquentOrganisation) {
            return null;
        }

        return $this->convertToModel($organisation);
    }

    public function getAll(): Collection
    {
        return EloquentOrganisation::all();
    }

    public function getEloquentOrganisationByUuid(string $uuid): ?EloquentOrganisation
    {
        return EloquentOrganisation::withoutGlobalScope(OrganisationAuthScope::class)->find($uuid);
    }

    public function getOrganisationByHpZoneCode(string $hpZoneCode): EloquentOrganisation
    {
        try {
            return EloquentOrganisation::where('hp_zone_code', $hpZoneCode)->firstOrFail();
        } catch (ModelNotFoundException $modelNotFoundException) {
            throw OrganisationNotFoundException::withHpZoneCode($hpZoneCode, $modelNotFoundException);
        }
    }

    public function getOrganisationByExternalId(string $externalId): ?EloquentOrganisation
    {
        return EloquentOrganisation::query()
            ->where('external_id', $externalId)
            ->first();
    }

    public function getOrganisationFromEloquentModel(EloquentOrganisation $eloquentOrganisation): Organisation
    {
        return $this->convertToModel($eloquentOrganisation);
    }

    private function createOutsourceOrganisationQueryBase(string $organisationUuid): EloquentBuilder
    {
        return EloquentOrganisation::query()
            ->withoutGlobalScope(OrganisationAuthScope::class)
            ->where(static function (EloquentBuilder $query) use ($organisationUuid): void {
                $query
                    ->where(static function (EloquentBuilder $query) use ($organisationUuid): void {
                        $query
                            ->where('organisation.has_outsource_toggle', 1)
                            ->where('organisation.is_available_for_outsourcing', 1)
                            ->where('organisation.uuid', '<>', $organisationUuid);
                    })
                    ->orWhereExists(static function (QueryBuilder $query) use ($organisationUuid): void {
                        $query
                            ->select(DB::raw(1))
                            ->from('organisation_outsource')
                            ->whereColumn('organisation_outsource.outsources_to_organisation_uuid', 'organisation.uuid')
                            ->where('organisation_outsource.organisation_uuid', $organisationUuid);
                    });
            });
    }

    public function getOutsourceOrganisations(string $organisationUuid): Collection
    {
        return $this->createOutsourceOrganisationQueryBase($organisationUuid)
            ->orderBy('name')
            ->get();
    }

    public function getOutsourceOrganisation(
        string $organisationUuid,
        string $outsourcesToOrganisationUuid,
    ): ?EloquentOrganisation {
        return $this->createOutsourceOrganisationQueryBase($organisationUuid)
            ->where('organisation.uuid', $outsourcesToOrganisationUuid)
            ->first();
    }

    private function convertToModel(EloquentOrganisation $eloquentOrganisation): Organisation
    {
        return $eloquentOrganisation->toOrganisation();
    }

    public function updateOrganisation(EloquentOrganisation $organisation): bool
    {
        return $organisation->save();
    }

    public function updateOrganisationBcoPhase(EloquentOrganisation $organisation, BCOPhase $bcoPhase): bool
    {
        $organisation->bcoPhase = $bcoPhase;

        return $organisation->save();
    }

    public function listOrganisations(): Collection
    {
        return $this->getOrganisationQueryBuilder()->get();
    }

    /**
     * @return Collection<string>
     */
    public function listOrganisationUuids(): Collection
    {
        return $this->getOrganisationQueryBuilder()
            ->select(['uuid'])
            ->pluck('uuid');
    }

    private function getOrganisationQueryBuilder(): EloquentBuilder
    {
        return EloquentOrganisation::query()
            ->withoutGlobalScope(OrganisationAuthScope::class)
            ->where('type', OrganisationType::regionalGGD());
    }
}
