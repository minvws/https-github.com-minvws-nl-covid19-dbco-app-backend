<?php

declare(strict_types=1);

namespace App\Services\Policy\RiskProfile;

use App\Services\Policy\PolicyGuideline\PolicyGuidelineHandler;

abstract class AbstractRiskProfileHandler
{
    public function __construct(private readonly PolicyGuidelineHandler $policyGuidelineHandler)
    {
    }

    public function getPolicyGuidelineHandler(): PolicyGuidelineHandler
    {
        return $this->policyGuidelineHandler;
    }
}
