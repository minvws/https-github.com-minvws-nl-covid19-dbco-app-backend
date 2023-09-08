<?php

declare(strict_types=1);

namespace App\Http\View\Composers;

use App\Services\AuthenticationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

use function config;

class HeaderComposer
{
    private AuthenticationService $authenticationService;
    private Request $request;

    public function __construct(AuthenticationService $authenticationService, Request $request)
    {
        $this->authenticationService = $authenticationService;
        $this->request = $request;
    }

    public function compose(View $view): void
    {
        $view->with('section', $this->request->segment(1));
        $view->with('environmentName', config('app.env_name'));
    }
}
