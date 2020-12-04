<?php

namespace App\Services;

use App\Models\BCOUser;
use App\Repositories\UserRepository;

class AuthenticationService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Returns the currently logged in user, or null if not logged in.
     * @return BCOUser|null
     */
    public function getAuthenticatedUser(): ?BCOUser
    {
        $dbUser = auth()->user();
        if ($dbUser) {
            return $this->userRepository->bcoUserFromEloquentUser($dbUser);
        }
        return null;
    }

    public function hasPlannerRole(): bool
    {
        $user = $this->getAuthenticatedUser();
        foreach($user->roles as $role) {
            if ($role == 'planner') {
                return true;
            }
        }
        return false;
    }

    /**
     * A user is considered a 'user' if he has at least the user role, but higher
     * roles are acceptable too.
     * @return bool
     */
    public function hasUserRole(): bool
    {
        $user = $this->getAuthenticatedUser();
        foreach($user->roles as $role) {
            if (in_array($role, ['user', 'planner', 'admin'])) {
                return true;
            }
        }
        return false;
    }

    public function logout()
    {
        return auth()->logout();
    }

}
