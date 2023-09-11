<?php

declare(strict_types=1);

namespace App\Services\Assignment;

use App\Services\Assignment\Enum\AssignmentModelEnum;
use App\Services\Assignment\Exception\AssignmentRuntimeException;

interface HasAssignmentToken
{
    public function hasToken(): bool;

    public function setToken(Token $token): self;

    /**
     * @throws AssignmentRuntimeException
     */
    public function getToken(): Token;

    /**
     * @param array<string> $uuids
     */
    public function allowedByToken(AssignmentModelEnum $model, array $uuids): bool;

    public function allowedCasesByToken(array $uuids): bool;
}
