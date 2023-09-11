<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\CaseUpdate;
use App\Models\Eloquent\CaseUpdateFragment;
use App\Services\Intake\IntakeConfig;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Factories\Factory;

use function count;

class CaseUpdateFactory extends Factory
{
    protected $model = CaseUpdate::class;

    public function withFragments(): self
    {
        return $this->afterCreating(function (CaseUpdate $caseUpdate): void {
            $fragmentNames = Container::getInstance()->get(IntakeConfig::class)->getAllowedCaseFragments();
            $fragmentNames = $this->faker->randomElements($fragmentNames, $this->faker->numberBetween(1, count($fragmentNames)));

            foreach ($fragmentNames as $fragmentName) {
                CaseUpdateFragment::factory()->create([
                    'case_update_uuid' => $caseUpdate->uuid,
                    'received_at' => $caseUpdate->received_at,
                    'name' => $fragmentName,
                ]);
            }
        });
    }

    public function definition(): array
    {
        return [
            'source' => 'publicPortal',
            'receivedAt' => $this->faker->dateTimeBetween('-5 minutes', '-1 minutes'),
            'createdAt' => $this->faker->dateTimeBetween('-1 minutes', 'now'),
        ];
    }
}
