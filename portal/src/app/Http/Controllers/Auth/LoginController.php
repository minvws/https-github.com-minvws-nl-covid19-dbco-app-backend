<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;

class LoginController extends Controller
{
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
        $user = Socialite::driver('identityhub')->user();

        Session::put('user', $user);

        return redirect()->intended('/');
    }

    public function stubAuthenticate()
    {
        $user = new \stdClass();
        $user->id = 0;
        $user->nickname = 'stub';
        $user->name = 'Stub User';

        Session::put('user', $user);

        return redirect()->intended('/');
    }
}
