<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent\Policy;

use App\Models\Policy\PolicyGuideline;
use App\Models\Policy\PolicyVersion;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\PolicyGuidelineReferenceField;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

class PolicyGuidelineFactory extends Factory
{
    protected $model = PolicyGuideline::class;

    public function definition(): array
    {
        $policyGuidelineReferenceField = $this->faker->randomElement(PolicyGuidelineReferenceField::all());
        $first = $this->faker->numberBetween(-20, -2);
        $middle = $this->faker->numberBetween($first + 1, 0);
        $last = $this->faker->numberBetween($middle + 1, 20);

        return [
            'uuid' => $this->faker->uuid(),
            'identifier' => $this->faker->unique()->words(asText: true),
            'policy_version_uuid' => PolicyVersion::factory(),
            'person_type' => PolicyPersonType::index(),
            'name' => $this->faker->word,
            'source_start_date_reference' => $policyGuidelineReferenceField,
            'source_start_date_addition' => $first,
            'source_end_date_reference' => $policyGuidelineReferenceField,
            'source_end_date_addition' => $middle,
            'contagious_start_date_reference' => $policyGuidelineReferenceField,
            'contagious_start_date_addition' => $middle,
            'contagious_end_date_reference' => $policyGuidelineReferenceField,
            'contagious_end_date_addition' => $last,
        ];
    }
}
