<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Place;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\ContextRelationship;

class ContextFactory extends Factory
{
    protected $model = Context::class;

    public function definition(): array
    {
        $createdAt = $this->faker->dateTime();

        return [
            'uuid' => $this->faker->uuid(),
            'label' => $this->faker->city,
            'created_at' => $createdAt,
            'updated_at' => $this->faker->dateTimeBetween($createdAt),
            'covidcase_uuid' => static function () {
                return EloquentCase::factory()->create();
            },
            'place_uuid' => static function () {
                return Place::factory()->create();
            },
            'relationship' => $this->faker->randomElement(ContextRelationship::all()),
            'other_relationship' => null,
            'explanation' => $this->faker->sentence(),
            'detailed_explanation' => $this->faker->sentence(),
            'remarks' => $this->faker->sentence(),
            'is_source' => $this->faker->boolean(),
            'schema_version' => static function (array $attributes) {
                /** @var EloquentCase $case */
                $case = EloquentCase::query()->find($attributes['covidcase_uuid']);
                return $case->getContextSchemaVersion()->getVersion();
            },
            'place_added_at' => $this->faker->dateTimeBetween($createdAt),
        ];
    }

    public function configure(): self
    {
        return $this->afterMaking(static function (Context $context): void {
            $context->general->label = $context->label;
            $context->general->relationship = $context->relationship;
            $context->general->otherRelationship = $context->other_relationship;
            $context->general->isSource = $context->is_source;
            $context->general->note = $context->explanation;
            $context->general->remarks = $context->remarks;
            $context->general->moments = [];
        });
    }
}
