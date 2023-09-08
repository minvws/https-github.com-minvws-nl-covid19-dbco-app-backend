<?php

declare(strict_types=1);

namespace App\Repositories\Policy;

use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use MinVWS\DBCO\Enum\Models\PolicyGuidelineReferenceField;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use Webmozart\Assert\Assert;

/**
 * @phpstan-import-type PolicyGuidelineAttributes from PolicyGuideline
 */
class PolicyGuidelineRepository
{
    /**
     * @return EloquentCollection<int,PolicyGuideline>
     */
    public function getPolicyGuidelinesByPolicyVersion(PolicyVersion $policyVersion, ?PolicyPersonType $personType = null): EloquentCollection
    {
        $personType = $personType ?? PolicyPersonType::index();

        return $policyVersion->policyGuidelines()
            ->with('policyVersion')
            ->where('person_type', $personType->value)
            ->orderBy('sort_order')->get();
    }

    public function getPolicyGuidelineByIdentifierAndPolicyVersion(string $identifier, PolicyVersion $policyVersion): ?PolicyGuideline
    {
        return $policyVersion->policyGuidelines()->where('identifier', $identifier)->first();
    }

    public function updatePolicyGuideline(PolicyGuideline $policyGuideline, array $attributes): PolicyGuideline
    {
        $policyGuideline->name = $attributes['name'];

        $policyGuideline->source_start_date_reference = PolicyGuidelineReferenceField::tryFrom(
            $attributes['sourceStartDateReference'] ?? '',
        ) ?? $policyGuideline->source_start_date_reference;
        $policyGuideline->source_start_date_addition = $attributes['sourceStartDateAddition'] ?? $policyGuideline->source_start_date_addition;

        $policyGuideline->source_end_date_reference = PolicyGuidelineReferenceField::tryFrom(
            $attributes['sourceEndDateReference'] ?? '',
        ) ?? $policyGuideline->source_end_date_reference;
        $policyGuideline->source_end_date_addition = $attributes['sourceEndDateAddition'] ?? $policyGuideline->source_end_date_addition;

        $policyGuideline->contagious_start_date_reference = PolicyGuidelineReferenceField::tryFrom(
            $attributes['contagiousStartDateReference'] ?? '',
        ) ?? $policyGuideline->contagious_start_date_reference;
        $policyGuideline->contagious_start_date_addition = $attributes['contagiousStartDateAddition'] ?? $policyGuideline->contagious_start_date_addition;

        $policyGuideline->contagious_end_date_reference = PolicyGuidelineReferenceField::tryFrom(
            $attributes['contagiousEndDateReference'] ?? '',
        ) ?? $policyGuideline->contagious_end_date_reference;
        $policyGuideline->contagious_end_date_addition = $attributes['contagiousEndDateAddition'] ?? $policyGuideline->contagious_end_date_addition;

        $policyGuideline->save();

        return $policyGuideline;
    }

    /**
     * @param array<PolicyGuidelineAttributes>|Collection<PolicyGuidelineAttributes> $data
     *
     * @return EloquentCollection<PolicyGuideline>
     *
     * @note The reason we use Eloquent updateOrCreate() over upsert(), is because upsert() does not emit Eloquent events.
     */
    public function upsertPolicyGuidelinesByPolicyVersion(PolicyVersion $policyVersion, array|Collection $data): EloquentCollection
    {
        Assert::allKeyExists($data, 'identifier', 'All policy guidelines (data) must have an "identifier" key');

        /** @var EloquentCollection<PolicyGuideline> */
        return LazyCollection::wrap($data)
            ->map(static function (array $guideline) use ($policyVersion): array {
                $guideline['policy_version_uuid'] = $policyVersion->uuid;

                return $guideline;
            })
            ->map(static fn (array $guideline): PolicyGuideline => PolicyGuideline::query()->updateOrCreate(
                Arr::only($guideline, ['identifier', 'policy_version_uuid']),
                $guideline,
            ))
            ->pipeInto(EloquentCollection::class);
    }

    public function loadMissing(PolicyGuideline $policyGuideline, string ...$relations): PolicyGuideline
    {
        return $policyGuideline->loadMissing(...$relations);
    }
}
