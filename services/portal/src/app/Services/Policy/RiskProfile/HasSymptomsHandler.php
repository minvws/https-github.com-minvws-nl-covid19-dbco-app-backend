<?php

declare(strict_types=1);

namespace App\Services\Policy\RiskProfile;

use App\Exceptions\Policy\UnsupportedPolicyFactObjectHandlerException;
use App\Services\Policy\ContactPolicyFacts;
use App\Services\Policy\IndexPolicyFacts;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

final class HasSymptomsHandler extends AbstractRiskProfileHandler implements RiskProfileHandler
{
    public function isApplicable(IndexPolicyFacts|ContactPolicyFacts $facts): bool
    {
        if (!$facts instanceof IndexPolicyFacts) {
            throw new UnsupportedPolicyFactObjectHandlerException();
        }

        return $facts->hasSymptoms === YesNoUnknown::yes();
    }
}
