<?php

declare(strict_types=1);

namespace App\Repositories\Policy;

use App\Models\Policy\PolicyVersion;
use App\Models\Policy\RiskProfile;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use Webmozart\Assert\Assert;

/**
 * @phpstan-import-type RiskProfileAttributes from RiskProfile
 */
class RiskProfileRepository
{
    /**
     * @return EloquentCollection<RiskProfile>
     */
    public function getRiskProfilesByPolicyVersion(PolicyVersion $policyVersion, ?PolicyPersonType $policyPersonType): EloquentCollection
    {
        /** @var EloquentCollection<RiskProfile> */
        return $policyVersion
            ->riskProfiles()
            ->when($policyPersonType, static function (Builder $query, ?PolicyPersonType $policyPersonType): void {
                $query->where('person_type_enum', $policyPersonType);
            })
            ->orderBy('sort_order')
            ->get();
    }

    public function getRiskProfileByUuidAndPolicyVersion(string $uuid, PolicyVersion $policyVersion): ?RiskProfile
    {
        return $policyVersion->riskProfiles()->where('uuid', $uuid)->first();
    }

    /**
     * @return EloquentCollection<RiskProfile>
     */
    public function getActiveRiskProfilesInApplianceOrderByPolicyVersion(PolicyVersion $policyVersion, ?PolicyPersonType $policyPersonType): EloquentCollection
    {
        /** @var EloquentCollection<RiskProfile> */
        return $policyVersion
            ->riskProfiles()
            ->when($policyPersonType, static function (Builder $query, ?PolicyPersonType $policyPersonType): void {
                $query->where('person_type_enum', $policyPersonType);
            })
            ->where('is_active', '1')
            ->orderBy('sort_order')
            ->get();
    }

    public function updateRiskProfileAttributes(RiskProfile $riskProfile, array $attributes): RiskProfile
    {
        $riskProfile->policy_guideline_uuid = $attributes['policyGuidelineUuid'];
        $riskProfile->save();

        return $riskProfile;
    }

    /**
     * @param array<RiskProfileAttributes>|Collection<RiskProfileAttributes> $data
     *
     * @return EloquentCollection<RiskProfile>
     *
     * @note The reason we use Eloquent updateOrCreate() over upsert(), is because upsert() does not emit Eloquent events.
     */
    public function upsertRiskProfilesByPolicyVersion(PolicyVersion $policyVersion, array|Collection $data): EloquentCollection
    {
        Assert::allKeyExists($data, 'risk_profile_enum', 'All risk profile (data) must have a "risk_profile_enum" key');

        /** @var EloquentCollection<RiskProfile> */
        return LazyCollection::wrap($data)
            ->map(static function (array $riskProfile) use ($policyVersion): array {
                $riskProfile['policy_version_uuid'] = $policyVersion->uuid;

                return $riskProfile;
            })
            ->map(static fn (array $riskProfile): RiskProfile => RiskProfile::query()->updateOrCreate(
                Arr::only($riskProfile, ['risk_profile_enum', 'policy_version_uuid']),
                $riskProfile,
            ))
            ->pipeInto(EloquentCollection::class);
    }
}
