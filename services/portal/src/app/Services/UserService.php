<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DepartmentClaimIdentityNotFoundException;
use App\Exceptions\IdentityHubConfigurationException;
use App\Exceptions\InvalidIdentityHubUserException;
use App\Helpers\Config;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use App\Models\OrganisationType;
use App\Providers\Auth\IdentityHubUser;
use App\Repositories\OrganisationRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Collection;
use Laravel\Socialite\Contracts\User;

use function array_key_exists;
use function config;
use function explode;
use function in_array;
use function sprintf;

class UserService
{
    private UserRepository $userRepository;
    private OrganisationRepository $organisationRepository;

    public function __construct(
        UserRepository $userRepository,
        OrganisationRepository $organisationRepository,
    ) {
        $this->userRepository = $userRepository;
        $this->organisationRepository = $organisationRepository;
    }

    /**
     * @throws InvalidIdentityHubUserException
     */
    public function upsertUserBySocaliteUser(User $socialiteUser): EloquentUser
    {
        if (!$socialiteUser instanceof IdentityHubUser) {
            throw new InvalidIdentityHubUserException('given user is not an identityHub user');
        }

        // Retrieve organisation-claim
        $eloquentOrganisation = $this->getEloquentOrganisationFromOrganisationClaim($socialiteUser);

        // check if department-claim given
        if (!$socialiteUser->getDepartments()->isEmpty()) {
            try {
                $eloquentOrganisationFromDepartment = $this->getEloquentOrganisationFromDepartmentClaim($socialiteUser);
                if (
                    $eloquentOrganisationFromDepartment->parentOrganisation
                    && $eloquentOrganisationFromDepartment->parentOrganisation->uuid !== $eloquentOrganisation->uuid
                ) {
                    throw new InvalidIdentityHubUserException('mismatch between department-claim and organisation-claim');
                }
                return $this->upsertUserByOrganisationUuid($socialiteUser, $eloquentOrganisationFromDepartment->uuid);
            } catch (DepartmentClaimIdentityNotFoundException) {
                // Organisation not found by departmentclaim. Continue and try organisation claim
            }
        }

        // If we get here we should have a RegionalGGD or a outsourceOrganisation
        return $this->upsertUserByOrganisationUuid($socialiteUser, $eloquentOrganisation->uuid);
    }

    /**
     * @throws InvalidIdentityHubUserException
     * @throws DepartmentClaimIdentityNotFoundException
     */
    private function getEloquentOrganisationFromDepartmentClaim(IdentityHubUser $socialiteUser): EloquentOrganisation
    {
        $socialiteUserDepartments = $socialiteUser->getDepartments();

        if ($socialiteUserDepartments->count() !== 1) {
            throw new InvalidIdentityHubUserException('expected exactly one department');
        }
        $socialiteUserDepartmentExternalId = $socialiteUserDepartments->first();

        $eloquentOrganisation = $this->organisationRepository->getOrganisationByExternalId($socialiteUserDepartmentExternalId);
        if ($eloquentOrganisation === null) {
            throw new DepartmentClaimIdentityNotFoundException(
                sprintf('department with external_id "%s" not found', $socialiteUserDepartmentExternalId),
            );
        }

        return $eloquentOrganisation;
    }

    /**
     * @throws InvalidIdentityHubUserException
     */
    private function getEloquentOrganisationFromOrganisationClaim(IdentityHubUser $socialiteUser): EloquentOrganisation
    {
        $socialiteUserOrganisations = $socialiteUser->getOrganisations();
        if ($socialiteUserOrganisations->count() !== 1) {
            throw new InvalidIdentityHubUserException('expected exactly one organisation');
        }
        $socialiteUserOrganisationExternalId = $socialiteUserOrganisations->first();

        $eloquentOrganisation = $this->organisationRepository->getOrganisationByExternalId($socialiteUserOrganisationExternalId);
        if ($eloquentOrganisation === null) {
            throw new InvalidIdentityHubUserException(
                sprintf('organisation with external_id "%s" not found', $socialiteUserOrganisationExternalId),
            );
        }

        return $eloquentOrganisation;
    }

    /**
     * @throws InvalidIdentityHubUserException
     */
    public function upsertUserByOrganisationUuid(IdentityHubUser $socialiteUser, string $organisationUuid): EloquentUser
    {
        return $this->userRepository->upsertUserByExternalId(
            $socialiteUser->getId(),
            $socialiteUser->getName(),
            $this->retrieveValidUserRoles($socialiteUser->getRoles(), $organisationUuid),
            $organisationUuid,
        );
    }

    /**
     * @throws InvalidIdentityHubUserException
     */
    private function retrieveValidUserRoles(Collection $roles, string $organisationUuid): array
    {
        $configuredRoles = config('authorization.roles');
        $userRoles = [];

        foreach ($roles as $role) {
            if (!array_key_exists('name', $role)) {
                continue;
            }

            foreach ($configuredRoles as $configuredRole => $configuredRoleValueDescription) {
                $configuredRoleValues = explode(',', $configuredRoleValueDescription);
                if (in_array($role['name'], $configuredRoleValues, true)) {
                    $userRoles[] = $configuredRole;
                }
            }
        }

        try {
            $this->assertRolesValid($userRoles, $organisationUuid);
        } catch (IdentityHubConfigurationException $e) {
            $userRoles = [];
        }

        return $userRoles;
    }

    /**
     * @throws InvalidIdentityHubUserException
     * @throws IdentityHubConfigurationException
     */
    private function assertRolesValid(array $roles, string $organisationUuid): void
    {
        $organisation = $this->organisationRepository->getEloquentOrganisationByUuid($organisationUuid);
        if ($organisation === null) {
            throw new InvalidIdentityHubUserException('Invalid organisation');
        }

        foreach ($roles as $role) {
            $roleIsNationwide = in_array($role, Config::array('authorization.nationwide_roles'), true);

            if ($roleIsNationwide && $organisation->type === OrganisationType::regionalGGD()) {
                throw new IdentityHubConfigurationException('Cannot assign nationwide role for regional GGD user.');
            }

            if (!$roleIsNationwide && $organisation->type !== OrganisationType::regionalGGD()) {
                throw new IdentityHubConfigurationException('Cannot assign regional role for non-regional GGD user.');
            }
        }
    }
}
