<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use Tests\Feature\FeatureTestCase;

class PurgeStaleChoresTest extends FeatureTestCase
{
    public function testRunWillDeleteStaleRecord(): void
    {
        $chore = $this->createChore([
            'resource_id' => $this->faker->uuid(),
        ]);

        $this->artisan('chores:purge-stale')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('chore', [
            'uuid' => $chore->uuid,
        ]);
    }

    public function testRunWillNotDeleteActiveRecord(): void
    {
        $chore = $this->createChore();

        $this->artisan('chores:purge-stale')
            ->assertExitCode(0);

        $this->assertDatabaseHas('chore', [
            'uuid' => $chore->uuid,
        ]);
    }
}
