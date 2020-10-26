<?php

namespace App\Services;

use App\Models\BCOUser;
use Illuminate\Support\Facades\Session;

class AuthenticationService
{
    public function getAuthenticatedUser(): BCOUser
    {
        return Session::get('user');
    }

    public function setAuthenticatedUser(BCOUser $user)
    {
        Session::put('user', $user);
    }
}
