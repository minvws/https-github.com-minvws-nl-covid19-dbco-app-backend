<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent\Policy;

use App\Models\Policy\PolicyVersion;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;

class PolicyVersionFactory extends Factory
{
    protected $model = PolicyVersion::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->word,
            'start_date' => $this->faker->date,
            'status' => $this->faker->randomElement(PolicyVersionStatus::all()),
        ];
    }
}
