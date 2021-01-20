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
    private AuthenticationService $authService;

    public function __construct(UserRepository $userRepository,
                                OrganisationRepository $organisationRepository,
                                AuthenticationService $authService)
    {
        $this->userRepository = $userRepository;
        $this->organisationRepository = $organisationRepository;
        $this->authService = $authService;
    }

    public function upsertUserByExternalId(string $externalId,
                                           string $name,
                                           array $roles,
                                           array $organisationExternalIds): EloquentUser
    {
        $organisationUuids = $this->organisationRepository->getOrganisationUuidsByExternalIds($organisationExternalIds);
        return $this->userRepository->upsertUserByExternalId($externalId, $name, $roles, $organisationUuids->all());
    }

    public function organisationUsers()
    {
        return $this->userRepository->getUsersByOrganisation($this->authService->getAuthenticatedUser());
    }
}
