<?php

declare(strict_types=1);

namespace App\Repositories\Policy;

use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use App\Models\Policy\RiskProfile;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\ContactRiskProfile;
use MinVWS\DBCO\Enum\Models\IndexRiskProfile;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

/**
 * @phpstan-import-type RiskProfileAttributes from RiskProfile
 */
class RiskProfilePopulator
{
    public function __construct(
        private readonly Connection $db,
        private readonly RiskProfileRepository $riskProfileRepository,
        private readonly PolicyGuidelineRepository $policyGuidelineRepository,
    )
    {
    }

    /**
     * @return EloquentCollection<RiskProfile>
     */
    public function populate(PolicyVersion $policyVersion): EloquentCollection
    {
        return $this->db->transaction(function () use ($policyVersion): EloquentCollection {
            $riskProfiles = $this->getIndexRiskProfileData($policyVersion)->merge($this->getContactRiskProfileData($policyVersion));

            return $this->riskProfileRepository->upsertRiskProfilesByPolicyVersion($policyVersion, $riskProfiles);
        });
    }

    /**
     * @return Collection<RiskProfileAttributes>
     */
    private function getIndexRiskProfileData(PolicyVersion $policyVersion): Collection
    {
        return Collection::make(IndexRiskProfile::all())
            ->map(function (IndexRiskProfile $riskProfileEnum, int $i) use ($policyVersion): array {
                return [
                    'risk_profile_enum' => $riskProfileEnum,
                    'policy_guideline_uuid' => $this->selectPolicyGuideline($riskProfileEnum, $policyVersion)?->uuid,
                    'name' => $riskProfileEnum->label,
                    'person_type_enum' => PolicyPersonType::index(),
                    'is_active' => true,
                    'sort_order' => ($i + 1) * 10,
                ];
            });
    }

    private function getContactRiskProfileData(PolicyVersion $policyVersion): Collection
    {
        return Collection::make(ContactRiskProfile::all())
            ->map(function (ContactRiskProfile $riskProfileEnum, int $i) use ($policyVersion): array {
                return [
                    'risk_profile_enum' => $riskProfileEnum,
                    'policy_guideline_uuid' => $this->selectPolicyGuideline($riskProfileEnum, $policyVersion)?->uuid,
                    'name' => $riskProfileEnum->label,
                    'person_type_enum' => PolicyPersonType::contact(),
                    'is_active' => true,
                    'sort_order' => ($i + 1) * 10,
                ];
            });
    }

    private function selectPolicyGuideline(IndexRiskProfile|ContactRiskProfile $riskProfileEnum, PolicyVersion $policyVersion): ?PolicyGuideline
    {
        return match ($riskProfileEnum) {
            IndexRiskProfile::hospitalAdmitted() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'symptomatic_extended',
                $policyVersion,
            ),
            IndexRiskProfile::hasSymptoms() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'symptomatic',
                $policyVersion,
            ),
            IndexRiskProfile::isImmunoCompromised() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'symptomatic',
                $policyVersion,
            ),
            IndexRiskProfile::noSymptoms() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'asymptomatic',
                $policyVersion,
            ),
            ContactRiskProfile::cat1VaccinatedDistancePossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat1VaccinatedDistanceNotPossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat1NotVaccinatedDistancePossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat1NotVaccinatedDistanceNotPossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat1VaccinationUnknownDistancePossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat1VaccinationUnknownDistanceNotPossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat2VaccinatedDistancePossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat2VaccinatedDistanceNotPossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat2NotVaccinatedDistancePossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat2NotVaccinatedDistanceNotPossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat2VaccinationUnknownDistancePossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat2VaccinationUnknownDistanceNotPossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat3VaccinatedDistancePossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat3VaccinatedDistanceNotPossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat3NotVaccinatedDistancePossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat3NotVaccinatedDistanceNotPossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat3VaccinationUnknownDistancePossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            ContactRiskProfile::cat3VaccinationUnknownDistanceNotPossible() => $this->policyGuidelineRepository->getPolicyGuidelineByIdentifierAndPolicyVersion(
                'no_quarantine',
                $policyVersion,
            ),
            default => null
        };
    }
}
