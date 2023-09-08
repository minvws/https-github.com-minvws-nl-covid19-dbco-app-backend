<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AuthenticationService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;

use function redirect;

class RootController extends Controller
{
    private AuthenticationService $authService;

    public function __construct(
        AuthenticationService $authService,
    ) {
        $this->authService = $authService;
    }

    /**
     * @throws AuthenticationException
     */
    public function rootRedirect(): RedirectResponse
    {
        if ($this->authService->hasRole('user') || $this->authService->hasRole('user_nationwide')) {
            return redirect()->intended('/cases');
        }

        if ($this->authService->hasRole('planner') || $this->authService->hasRole('planner_nationwide')) {
            return redirect()->intended('/planner');
        }

        if ($this->authService->hasRole('compliance')) {
            return redirect()->intended('/compliance');
        }

        if ($this->authService->hasRole('clusterspecialist')) {
            return redirect()->intended('/places');
        }

        if ($this->authService->hasRole('medical_supervisor_nationwide') || $this->authService->hasRole('medical_supervisor')) {
            return redirect()->intended('/medische-supervisie');
        }

        if ($this->authService->hasRole('conversation_coach_nationwide') || $this->authService->hasRole('conversation_coach')) {
            return redirect()->intended('/gesprekscoach');
        }

        if ($this->authService->hasRole('callcenter') || $this->authService->hasRole('callcenter_expert')) {
            return redirect()->intended('/dossierzoeken');
        }

        if ($this->authService->hasRole('admin')) {
            return redirect()->intended('/beheren');
        }

        return redirect()->intended('cases');
    }
}
