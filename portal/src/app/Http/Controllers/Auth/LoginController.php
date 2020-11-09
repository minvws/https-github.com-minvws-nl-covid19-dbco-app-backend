<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BCOUser;
use App\Models\Eloquent\EloquentUser;
use App\Services\AuthenticationService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    private AuthenticationService $authService;
    private UserService $userService;

    public function __construct(AuthenticationService $authService,
                                UserService $userService)
    {
        $this->authService = $authService;
        $this->userService = $userService;
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
        // Cast the user to an array so that we can read the custom properties we added
        $socialiteUser = (array)Socialite::driver('identityhub')->user();

        $user = $this->userService->upsertUserByExternalId($socialiteUser['id'],
                                                          $socialiteUser['name'],
                                                          $socialiteUser['email'],
                                                          $socialiteUser['roles'],
                                                          $socialiteUser['organisations']);

        Auth::login($user, true);

        return redirect()->intended('/');
    }

    public function stubAuthenticate(Request $request)
    {
        $roles = config('authorization.roles');
        $demoUuids = [
            'user' => '00000000-0000-0000-0000-000000000001',
            'planner' => '00000000-0000-0000-0000-000000000002',
            'admin' => '00000000-0000-0000-0000-000000000003'
        ];
        $demoNames = [
            'user' => 'Demo Gebruiker',
            'planner' => 'Demo Werkverdeler',
            'admin' => 'Demo Beheerder'
        ];
        $desiredRole = $request->input('role');

        $user = $this->userService->upsertUserByExternalId($demoUuids[$desiredRole],
            $demoNames[$desiredRole],
            'dummy@gebruiker.tst',
            [$roles[$desiredRole]],
            ['999999']);
        Auth::login($user, true);

        return redirect()->intended('/');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->intended('/');
    }
}
