<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Jobs\UpdatePlaceCounters;
use Illuminate\Support\Facades\Bus;
use Tests\Feature\FeatureTestCase;

class SyncPlaceCountersTest extends FeatureTestCase
{
    public function testRunWithoutRecords(): void
    {
        $this->artisan('place-counters:sync')
            ->assertExitCode(0);
    }

    public function testRunWithMultipleRecords(): void
    {
        $place1 = $this->createPlace();
        $place2 = $this->createPlace();

        $this->artisan('place-counters:sync')
            ->assertExitCode(0);

        $this->assertDatabaseHas('place_counters', [
            'place_uuid' => $place1->uuid,
        ]);

        $this->assertDatabaseHas('place_counters', [
            'place_uuid' => $place2->uuid,
        ]);
    }

    public function testRunDispatchesJob(): void
    {
        Bus::fake([UpdatePlaceCounters::class]);

        // A Place must exist, else it won't dispatch a job
        $this->createPlace();

        $this->artisan('place-counters:sync');

        Bus::assertDispatched(UpdatePlaceCounters::class);
    }

    public function testDispatchJobWithUnknownUuidWillSkip(): void
    {
        Bus::dispatch(new UpdatePlaceCounters($this->faker->uuid()));
        $this->assertDatabaseCount('place_counters', 0);
    }
}
