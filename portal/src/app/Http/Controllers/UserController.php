<?php

namespace App\Http\Controllers;

use App\Services\AuthenticationService;

class UserController extends Controller
{
    private AuthenticationService $authenticationService;

    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    public function profile()
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $roles = config('authorization.roles');

        return view('profile', ['user' => $user, 'roles' => $roles]);
    }
}
