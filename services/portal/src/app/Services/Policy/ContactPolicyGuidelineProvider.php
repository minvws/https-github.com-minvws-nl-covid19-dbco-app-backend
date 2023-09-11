<?php

declare(strict_types=1);

namespace App\Services\Policy;

use App\Exceptions\Policy\PolicyFactMissingException;
use App\Exceptions\Policy\RiskProfileMatchNotFoundException;
use App\Models\Policy\PolicyVersion;
use App\Services\Policy\PolicyGuideline\PolicyGuidelineHandler;
use App\Services\Policy\RiskProfile\ContactRiskProfileHandlerFactory;
use App\Services\PolicyVersionService;
use App\Services\RiskProfileService;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

class ContactPolicyGuidelineProvider
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
    public function getByPolicyVersionApplicableByFacts(ContactPolicyFacts $facts, ?PolicyVersion $policyVersion = null): PolicyGuidelineHandler
    {
        $riskProfileModels = $this->riskProfileService->getActiveRiskProfilesInApplianceOrderByPolicyVersion(
            $policyVersion ?? $this->policyVersionService->getLatestPolicyVersion(),
            PolicyPersonType::contact(),
        );

        foreach ($riskProfileModels as $riskProfileModel) {
            $contactRiskProfileHandler = ContactRiskProfileHandlerFactory::create($riskProfileModel);

            if ($contactRiskProfileHandler->isApplicable($facts)) {
                return $contactRiskProfileHandler->getPolicyGuidelineHandler();
            }
        }

        throw new RiskProfileMatchNotFoundException();
    }
}
