<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\Place;
use MinVWS\DBCO\Enum\Models\Permission;

class PlacePolicy
{
    public function create(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::placeCreate()->value);
    }

    public function delete(EloquentUser $eloquentUser, Place $place): bool
    {
        return $eloquentUser->can(Permission::placeDelete()->value) && $this->isSameOrganisation($eloquentUser, $place);
    }

    public function edit(EloquentUser $eloquentUser, Place $place): bool
    {
        if ($eloquentUser->can(Permission::placeEditNotOwnedByOrganisation()->value)) {
            return true;
        }

        return $this->isSameOrganisation($eloquentUser, $place) && $eloquentUser->can(Permission::placeEditOwnedByOrganisation()->value);
    }

    public function list(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::placeList()->value);
    }

    public function search(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::placeSearch()->value);
    }

    public function merge(EloquentUser $eloquentUser, Place $place): bool
    {
        return $eloquentUser->can(Permission::placeMerge()->value) && $this->isSameOrganisation($eloquentUser, $place);
    }

    public function verify(EloquentUser $eloquentUser, ?Place $place = null): bool
    {
        if (!$eloquentUser->can(Permission::placeVerify()->value)) {
            return false;
        }

        if ($place === null) {
            return true;
        }

        return $this->checkUserRegionForPlace($eloquentUser, $place);
    }

    private function checkUserRegionForPlace(EloquentUser $eloquentUser, Place $place): bool
    {
        /** @var EloquentOrganisation $organisation */
        foreach ($eloquentUser->organisations as $organisation) {
            if ($organisation->uuid === $place->organisation_uuid) {
                return true;
            }
        }
        return false;
    }

    // ----- SECTIONS -----####

    public function sectionList(EloquentUser $eloquentUser): bool
    {
        return $eloquentUser->can(Permission::placeSectionList()->value);
    }

    public function sectionCreate(EloquentUser $eloquentUser, Place $place): bool
    {
        if ($eloquentUser->can(Permission::placeSectionCreateNotOwnedByOrganisation()->value)) {
            return true;
        }

        return $this->isSameOrganisation($eloquentUser, $place) && $eloquentUser->can(
            Permission::placeSectionCreateOwnedByOrganisation()->value,
        );
    }

    public function sectionEdit(EloquentUser $eloquentUser, Place $place): bool
    {
        if ($eloquentUser->can(Permission::placeSectionEditNotOwnedByOrganisation()->value)) {
            return true;
        }

        return $this->isSameOrganisation($eloquentUser, $place) && $eloquentUser->can(
            Permission::placeSectionEditOwnedByOrganisation()->value,
        );
    }

    public function sectionDelete(EloquentUser $eloquentUser, Place $place): bool
    {
        return $eloquentUser->can(Permission::placeSectionDelete()->value) && $this->isSameOrganisation($eloquentUser, $place);
    }

    public function sectionMerge(EloquentUser $eloquentUser, Place $place): bool
    {
        return $eloquentUser->can(Permission::placeSectionMerge()->value) && $this->isSameOrganisation($eloquentUser, $place);
    }

    private function isSameOrganisation(EloquentUser $eloquentUser, Place $place): bool
    {
        $organisation = $eloquentUser->getOrganisation();

        if ($organisation === null) {
            return false;
        }

        return $place->organisation_uuid === $organisation->uuid;
    }
}
