<?php

declare(strict_types=1);

namespace App\Services\Policy;

use App\Exceptions\Policy\PolicyFactMissingException;
use App\Exceptions\Policy\RiskProfileMatchNotFoundException;
use App\Models\Policy\PolicyVersion;
use App\Services\Policy\PolicyGuideline\PolicyGuidelineHandler;
use App\Services\Policy\RiskProfile\IndexRiskProfileHandlerFactory;
use App\Services\PolicyVersionService;
use App\Services\RiskProfileService;

class IndexPolicyGuidelineProvider
{
    public function __construct(
        private readonly PolicyVersionService $policyVersionService,
        private readonly RiskProfileService $riskProfileService,
    ) {
    }

    /**
     * @throws RiskProfileMatchNotFoundException
     * @throws PolicyFactMissingException
     */
    public function getByPolicyVersionApplicableByFacts(IndexPolicyFacts $facts, ?PolicyVersion $policyVersion = null): PolicyGuidelineHandler
    {
        $riskProfileModels = $this->riskProfileService->getActiveRiskProfilesInApplianceOrderByPolicyVersion(
            $policyVersion ?? $this->policyVersionService->getLatestPolicyVersion(),
        );

        foreach ($riskProfileModels as $riskProfileModel) {
            $riskProfileHandler = IndexRiskProfileHandlerFactory::create($riskProfileModel);
            if ($riskProfileHandler->isApplicable($facts)) {
                return $riskProfileHandler->getPolicyGuidelineHandler();
            }
        }

        throw new RiskProfileMatchNotFoundException();
    }
}
