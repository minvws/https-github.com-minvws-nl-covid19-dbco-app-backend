<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AuthenticationService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\View\View;
use MinVWS\Audit\Services\AuditService;

use function config;
use function view;

class UserController extends Controller
{
    private AuthenticationService $authenticationService;

    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * @throws AuthenticationException
     */
    public function profile(AuditService $auditService): View
    {
        $auditService->setEventExpected(false);

        $user = $this->authenticationService->getAuthenticatedUser();
        $roles = config('authorization.roles');

        return view('profile', ['user' => $user, 'roles' => $roles]);
    }
}
