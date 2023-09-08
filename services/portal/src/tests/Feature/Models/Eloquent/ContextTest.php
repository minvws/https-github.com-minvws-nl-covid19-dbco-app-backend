<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Eloquent;

use App\Jobs\UpdatePlaceCounters;
use Illuminate\Support\Facades\Queue;
use MinVWS\DBCO\Enum\Models\ContextRelationship;
use Tests\Feature\FeatureTestCase;

class ContextTest extends FeatureTestCase
{
    public function testContextObserverOnCreate(): void
    {
        Queue::fake();

        $place = $this->createPlace();
        $this->createContextForPlace($place);

        Queue::assertPushed(
            UpdatePlaceCounters::class,
            static function (UpdatePlaceCounters $updatePlaceCounters) use ($place) {
                return $updatePlaceCounters->uniqueId() === $place->uuid;
            },
        );
    }

    public function testContextObserverOnUpdate(): void
    {
        $place = $this->createPlace();
        $context = $this->createContextForPlace($place, [
            'relationship' => null,
        ]);

        Queue::fake();

        $context->relationship = $this->faker->randomElement(ContextRelationship::all());
        $context->save();

        Queue::assertPushed(
            UpdatePlaceCounters::class,
            static function (UpdatePlaceCounters $updatePlaceCounters) use ($place) {
                return $updatePlaceCounters->uniqueId() === $place->uuid;
            },
        );
    }

    public function testContextObserverOnDelete(): void
    {
        $place = $this->createPlace();
        $context = $this->createContextForPlace($place);

        Queue::fake();

        $context->delete();

        Queue::assertPushed(
            UpdatePlaceCounters::class,
            static function (UpdatePlaceCounters $updatePlaceCounters) use ($place) {
                return $updatePlaceCounters->uniqueId() === $place->uuid;
            },
        );
    }
}
