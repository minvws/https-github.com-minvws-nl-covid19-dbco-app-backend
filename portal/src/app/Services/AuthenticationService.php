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

    public function isPlanner(): bool
    {
        $user = $this->getAuthenticatedUser();
        $roles = config('authorization.roles');
        foreach($user->roles as $role) {
            if ($role == $roles['planner']) {
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
