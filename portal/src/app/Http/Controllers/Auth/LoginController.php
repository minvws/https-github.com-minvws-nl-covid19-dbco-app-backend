<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BCOUser;
use App\Services\AuthenticationService;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;

class LoginController extends Controller
{
    private AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Redirect the user to the IdentityHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('identityhub')->redirect();
    }

    /**
     * Obtain the user information from IdentityHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $socialiteUser = Socialite::driver('identityhub')->user();
        $user = new BCOUser();
        $user->id = $socialiteUser->getId();
        $user->name = $socialiteUser->getName();

        $this->authService->setAuthenticatedUser($user);

        return redirect()->intended('/');
    }

    public function stubAuthenticate()
    {
        $user = new BCOUser();
        $user->id = 0;
        $user->name = 'Dummy User';

        $this->authService->setAuthenticatedUser($user);

        return redirect()->intended('/');
    }
}
