<?php

declare(strict_types=1);

namespace App\Services\Assignment;

use App\Services\Assignment\Enum\AssignmentModelEnum;
use App\Services\Assignment\Exception\AssignmentRuntimeException;

trait AssignmentTokenable
{
    use AssignmentAllowedByToken;

    protected Token $assignmentToken;

    public function hasToken(): bool
    {
        return isset($this->assignmentToken);
    }

    public function setToken(Token $token): self
    {
        $this->assignmentToken = $token;

        return $this;
    }

    public function getToken(): Token
    {
        if (!$this->hasToken()) {
            throw new AssignmentRuntimeException('This user does not have a token!');
        }

        return $this->assignmentToken;
    }

    public function allowedByToken(AssignmentModelEnum $model, array $uuids): bool
    {
        if (!$this->hasToken()) {
            return false;
        }

        return $this->allowedByModel($this->getToken(), $model, $uuids);
    }

    public function allowedCasesByToken(array $uuids): bool
    {
        return $this->allowedByToken(AssignmentModelEnum::Case_, $uuids);
    }

    public function allowedCaseNotesByToken(array $caseUuids): bool
    {
        return $this->allowedByToken(AssignmentModelEnum::Note, $caseUuids) || $this->allowedCasesByToken($caseUuids);
    }

    public function allowedCaseCallToActionsByToken(array $caseUuids): bool
    {
        return $this->allowedByToken(AssignmentModelEnum::CallToAction, $caseUuids) || $this->allowedCasesByToken($caseUuids);
    }
}
