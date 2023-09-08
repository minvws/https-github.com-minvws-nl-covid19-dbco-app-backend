<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\Note;
use App\Models\Eloquent\Timeline;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\CaseNoteType;

class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'case_created_at' => $this->faker->dateTime('-1 week'),
            'uuid' => $this->faker->uuid(),
            'user_uuid' => static function () {
                return EloquentUser::factory()->create();
            },
            'user_name' => $this->faker->name(),
            'organisation_name' => $this->faker->company(),
            'updated_at' => $this->faker->dateTimeBetween('-2 days'),
            'created_at' => $this->faker->dateTimeBetween('-6 days'),
            'type' => $this->faker->randomElement(CaseNoteType::all()),
            'note' => $this->faker->text,
        ];
    }

    public function configure(): self
    {
        return $this->afterMaking(static function (Note $note): void {
            /** @var Timeline $timeline */
            $timeline = Timeline::make();
            $timeline->case_uuid = $note->case_uuid;
            $timeline->timelineable()->associate($note);
            $timeline->save();
        });
    }
}
