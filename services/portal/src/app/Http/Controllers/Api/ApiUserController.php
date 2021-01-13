<?php

namespace App\Http\Controllers\Api;

use App\Services\UserService;

use Illuminate\Http\Response;


class ApiUserController extends ApiController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function assignableUsers()
    {
        $assignableUsers = $this->userService->organisationUsers();

        return response()->json(['users' => $assignableUsers], Response::HTTP_OK);
    }
}
