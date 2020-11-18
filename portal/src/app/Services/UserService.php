<?php

namespace App\Services;

use App\Models\BCOUser;
use App\Models\Eloquent\EloquentUser;
use App\Repositories\OrganisationRepository;
use App\Repositories\UserRepository;

class UserService
{
    private UserRepository $userRepository;
    private OrganisationRepository $organisationRepository;

    public function __construct(UserRepository $userRepository,
                                OrganisationRepository $organisationRepository)
    {
        $this->userRepository = $userRepository;
        $this->organisationRepository = $organisationRepository;
    }

    public function upsertUserByExternalId(string $externalId,
                                           string $name,
                                           array $roles,
                                           array $organisationExternalIds): EloquentUser
    {
        $organisationUuids = $this->organisationRepository->getOrganisationUuidsByExternalIds($organisationExternalIds);
        return $this->userRepository->upsertUserByExternalId($externalId, $name, $roles, $organisationUuids->all());
    }
}
