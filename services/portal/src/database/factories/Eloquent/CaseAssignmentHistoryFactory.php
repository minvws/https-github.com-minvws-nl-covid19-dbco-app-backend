<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\CaseAssignmentHistory;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\Timeline;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaseAssignmentHistoryFactory extends Factory
{
    protected $model = CaseAssignmentHistory::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'assigned_organisation_uuid' => static function () {
                return EloquentOrganisation::factory()->create();
            },
            'assigned_at' => $this->faker->dateTimeBetween('-6 days'),
        ];
    }

    public function configure(): self
    {
        return $this->afterMaking(static function (CaseAssignmentHistory $assignmentHistory): void {
            /** @var Timeline $timeline */
            $timeline = Timeline::make();
            $timeline->case_uuid = $assignmentHistory->covidcase_uuid;
            $timeline->created_at = $assignmentHistory->assigned_at;
            $timeline->updated_at = $assignmentHistory->assigned_at;
            $timeline->timelineable()->associate($assignmentHistory);
            $timeline->save();
        });
    }
}
