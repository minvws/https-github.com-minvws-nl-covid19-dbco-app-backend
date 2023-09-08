<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent\Policy;

use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use App\Models\Policy\RiskProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\ContactRiskProfile;
use MinVWS\DBCO\Enum\Models\IndexRiskProfile;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

class RiskProfileFactory extends Factory
{
    protected $model = RiskProfile::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'policy_version_uuid' => PolicyVersion::factory(),
            'policy_guideline_uuid' => PolicyGuideline::factory(),
            'name' => $this->faker->unique()->words(asText: true),
            'person_type_enum' => static function (array $attributes) {
                /** @var PolicyGuideline $policyGuideline */
                $policyGuideline = PolicyGuideline::query()->findOrFail($attributes['policy_guideline_uuid']);

                return $policyGuideline->person_type;
            },
            'risk_profile_enum' => function (array $attributes) {
                return match ($attributes['person_type_enum'] ?? null) {
                    PolicyPersonType::index() => $this->faker->randomElement(IndexRiskProfile::all()),
                    PolicyPersonType::contact() => $this->faker->randomElement(ContactRiskProfile::all()),
                    default => $this->faker->randomElement(IndexRiskProfile::all()),
                };
            },
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 10_000),
        ];
    }
}
