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

        return view('profile', ['user' => $user]);
    }
}
