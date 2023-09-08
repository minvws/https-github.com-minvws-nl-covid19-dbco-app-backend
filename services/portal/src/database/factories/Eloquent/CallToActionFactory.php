<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\CallToAction;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class CallToActionFactory extends Factory
{
    protected $model = CallToAction::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'subject' => $this->faker->text(50),
            'description' => $this->faker->sentence(),
            'created_by' => null,
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ];
    }
}
