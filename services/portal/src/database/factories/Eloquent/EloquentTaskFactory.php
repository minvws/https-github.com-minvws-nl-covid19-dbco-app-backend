<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\InformedBy;
use MinVWS\DBCO\Enum\Models\TaskGroup;

class EloquentTaskFactory extends Factory
{
    protected $model = EloquentTask::class;

    public function newModel(array $attributes = []): EloquentTask
    {
        /** @var EloquentTask $task */
        $task = EloquentTask::getSchema()
            ->getVersion($attributes['schema_version'] ?? EloquentTask::getSchema()->getCurrentVersion()->getVersion())
            ->newInstance();

        $task->forceFill($attributes);

        return $task;
    }

    public function definition(): array
    {
        if (CarbonImmutable::hasTestNow()) {
            $createdAt = CarbonImmutable::now();
            $updatedAt = CarbonImmutable::now();
        } else {
            $createdAt = new CarbonImmutable($this->faker->dateTimeBetween('-27 days'));
            $updatedAt = $this->faker->dateTimeBetween($createdAt);
        }

        return [
            'uuid' => $this->faker->uuid(),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'case_uuid' => static function () {
                return EloquentCase::factory()->create();
            },
            'status' => $this->faker->randomElement([
                Task::TASK_STATUS_OPEN,
                Task::TASK_STATUS_CLOSED,
                Task::TASK_STATUS_DELETED,
            ]),
            'task_type' => 'contact',
            'task_group' => TaskGroup::contact(),
            'source' => $this->faker->randomElement([
                'app',
                'portal',
            ]),
            'label' => $this->faker->randomElement([
                'complete',
                'task-without-answers',
                'incomplete',
                'contactable',
            ]),
            'task_context' => $this->faker->jobTitle,
            'category' => $this->faker->randomElement(ContactCategory::all()),
            'date_of_last_exposure' => $this->faker->dateTimeBetween(CarbonImmutable::parse('-30 days'), $createdAt),
            'communication' => $this->faker->randomElement(InformedBy::all()),
            'schema_version' => EloquentTask::getSchema()->getCurrentVersion()->getVersion(),
        ];
    }
}
