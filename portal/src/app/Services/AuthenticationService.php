<?php

namespace App\Services;

use App\Models\BCOUser;
use Illuminate\Support\Facades\Session;

class AuthenticationService
{
    /**
     * Returns the currently logged in user, or null if not logged in.
     * @return BCOUser|null
     */
    public function getAuthenticatedUser(): ?BCOUser
    {
        return Session::get('user');
    }

    public function setAuthenticatedUser(BCOUser $user)
    {
        Session::put('user', $user);
    }
}
