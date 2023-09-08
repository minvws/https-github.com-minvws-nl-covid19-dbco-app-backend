<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Policy\PolicyVersion;
use App\Models\Policy\RiskProfile;
use App\Repositories\Policy\RiskProfileRepository;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

class RiskProfileService
{
    public function __construct(private readonly RiskProfileRepository $riskProfileRepository)
    {
    }

    /**
     * @return Collection<RiskProfile>
     */
    public function getRiskProfilesByPolicyVersion(PolicyVersion $policyVersion, ?PolicyPersonType $policyPersonType): Collection
    {
        return $this->riskProfileRepository->getRiskProfilesByPolicyVersion($policyVersion, $policyPersonType);
    }

    /**
     * @return Collection<RiskProfile>
     */
    public function getActiveRiskProfilesInApplianceOrderByPolicyVersion(PolicyVersion $policyVersion, ?PolicyPersonType $policyPersonType = null): Collection
    {
        $policyPersonType ??= PolicyPersonType::index();

        return $this->riskProfileRepository->getActiveRiskProfilesInApplianceOrderByPolicyVersion($policyVersion, $policyPersonType);
    }

    public function updateRiskProfile(RiskProfile $riskProfile, array $attributes): RiskProfile
    {
        return $this->riskProfileRepository->updateRiskProfileAttributes($riskProfile, $attributes);
    }
}
