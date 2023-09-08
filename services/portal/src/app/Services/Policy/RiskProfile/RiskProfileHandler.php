<?php

declare(strict_types=1);

namespace App\Services\Policy\RiskProfile;

use App\Services\Policy\ContactPolicyFacts;
use App\Services\Policy\IndexPolicyFacts;
use App\Services\Policy\PolicyGuideline\PolicyGuidelineHandler;

interface RiskProfileHandler
{
    public function isApplicable(IndexPolicyFacts|ContactPolicyFacts $facts): bool;

    public function getPolicyGuidelineHandler(): PolicyGuidelineHandler;
}
