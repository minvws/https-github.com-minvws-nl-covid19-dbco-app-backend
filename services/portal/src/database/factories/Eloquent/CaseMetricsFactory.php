<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\CaseMetrics;
use App\Models\Eloquent\EloquentOrganisation;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

use function config;

class CaseMetricsFactory extends Factory
{
    protected $model = CaseMetrics::class;

    public function definition(): array
    {
        /** @var string|int $numDaysInPast */
        $numDaysInPast = config('casemetrics.created_archived_days_in_past');
        $date = CarbonImmutable::parse($this->faker->dateTimeBetween("-$numDaysInPast days"));

        return [
            'date' => $date,
            'organisation_uuid' => static function () {
                return EloquentOrganisation::factory()->create();
            },
            'created_count' => $this->faker->numberBetween(0, 50_000),
            'archived_count' => $this->faker->numberBetween(0, 50_000),
            'refreshed_at' => $date->addDay(),
        ];
    }
}
