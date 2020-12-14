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
        return $this->hasRole('planner');
    }

    /**
     * Check if the logged in user has the user role.
     * @return bool
     */
    public function hasUserRole(): bool
    {
        return $this->hasRole('user');
    }

    /**
     * Check if the logged in user has a certain role
     * @param string $role The role alias ('user', 'admin', 'planner' etc)
     * @return bool
     */
    public function hasRole(string $requiredRole): bool
    {
        $user = $this->getAuthenticatedUser();
        foreach($user->roles as $role) {
            if ($requiredRole == $role) {
                return true;
            }
        }
        return false;
    }

    public function logout()
    {
        return auth()->logout();
    }

    public function getCopyData(BCOUser $user)
    {
        return "Naam (volledig): ".$user->name;
    }

}
