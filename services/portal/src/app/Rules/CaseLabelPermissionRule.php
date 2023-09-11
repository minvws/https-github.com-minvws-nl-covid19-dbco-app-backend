<?php

declare(strict_types=1);

namespace App\Rules;

use App\Services\AuthenticationService;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;

class CaseLabelPermissionRule implements Rule
{
    private Collection $allowedCaseLabelUuids;

    public function __construct(AuthenticationService $authenticationService)
    {
        $organisation = $authenticationService->getRequiredSelectedOrganisation();
        $this->allowedCaseLabelUuids = $organisation->caseLabels->pluck('uuid');
    }

    /**
     * @inheritdoc
     */
    public function passes($attribute, $value): bool
    {
        return $this->allowedCaseLabelUuids->contains($value);
    }

    public function message(): string
    {
        return 'no permission to edit entity for :attribute';
    }
}
