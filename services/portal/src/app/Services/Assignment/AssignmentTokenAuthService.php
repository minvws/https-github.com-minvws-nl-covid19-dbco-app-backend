<?php

declare(strict_types=1);

namespace App\Services\Assignment;

use App\Models\Eloquent\EloquentUser;
use App\Services\Assignment\Enum\AssignmentModelEnum;

interface AssignmentTokenAuthService
{
    public function hasToken(): bool;

    public function getToken(): Token;

    /**
     * @param array<int,string> $uuids
     */
    public function allowed(AssignmentModelEnum $model, array $uuids, EloquentUser $user): bool;

    /**
     * @param array<int,string> $uuids
     */
    public function allowedCases(array $uuids, EloquentUser $user): bool;
}
