<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Models\Eloquent\EloquentUser;
use Database\Seeders\DummySeeder;
use Illuminate\Testing\PendingCommand;
use Tests\Feature\FeatureTestCase;

class TestDataUpdateDemoUsersCommandTest extends FeatureTestCase
{
    public function testCommand(): void
    {
        /** @var EloquentUser $eloquentUser */
        $eloquentUser = EloquentUser::query()->where(['uuid' => DummySeeder::DEMO_DATACATALOG])->first();
        $eloquentUser->name = $this->faker->name();
        $eloquentUser->save();

        /** @var PendingCommand $artisan */
        $artisan = $this->artisan('test-data:update-users');

        $artisan->expectsOutput('Updating demo-users: start')
            ->expectsOutput('Updating demo-users: done')
            ->assertExitCode(0)
            ->execute();

        $this->assertDatabaseHas('bcouser', [
            'uuid' => DummySeeder::DEMO_DATACATALOG,
            'name' => 'Demo GGD1 Datacatalogus',
            'roles' => 'datacatalog',
        ]);
    }
}
