<?php

namespace App\Http\View\Composers;

use App\Services\AuthenticationService;
use Illuminate\View\View;

class IdentityBarComposer
{
    private AuthenticationService $authenticationService;

    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $user = $this->authenticationService->getAuthenticatedUser();
        $view->with('userName', $user->name);
    }
}
