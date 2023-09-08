<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\PurgeSoftDeletedModels;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\PendingCommand;
use Tests\Feature\FeatureTestCase;

use function array_map;

class PurgeSoftDeletedModelsTest extends FeatureTestCase
{
    public function testPurgingModelsNoData(): void
    {
        /** @var PendingCommand $artisan */
        $artisan = $this->artisan('models:purge');

        $artisan->expectsOutput('Purging soft deleted models...')
            ->expectsOutput('Purged 0 covidcases')
            ->expectsOutput('Purged 0 tasks')
            ->assertExitCode(0);
    }

    public function testPurgingModels(): void
    {
        $today = CarbonImmutable::now()->subDay();
        $purgeDaysAgo = CarbonImmutable::now()->subDays(PurgeSoftDeletedModels::PURGE_AFTER_DAYS)->subDay();
        $user = $this->createUser();

        $caseOne = $this->createCaseForUser($user, ['deleted_at' => null]);
        $caseTwo = $this->createCaseForUser($user, ['deleted_at' => $purgeDaysAgo]);
        $caseThree = $this->createCaseForUser($user, ['deleted_at' => $today]);
        $caseFour = $this->createCaseForUser($user, ['deleted_at' => $purgeDaysAgo]);

        $taskOne = $this->createTaskForCase($caseOne, ['deleted_at' => $today]);
        $taskTwo = $this->createTaskForCase($caseOne, ['deleted_at' => null]);
        $taskThree = $this->createTaskForCase($caseOne, ['deleted_at' => $purgeDaysAgo]);

        /** @var PendingCommand $artisan */
        $artisan = $this->artisan('models:purge');

        $artisan->expectsOutput('Purging soft deleted models...')
            ->expectsOutput('Purged 2 covidcases')
            ->expectsOutput('Purged 1 task')
            ->assertExitCode(0)
            ->execute();

        $this->assertDatabaseCount('task', 2);
        $this->assertDatabaseHas('task', ['uuid' => $taskOne->uuid]);
        $this->assertDatabaseHas('task', ['uuid' => $taskTwo->uuid]);
        $this->assertDatabaseMissing('task', ['uuid' => $taskThree->uuid]);

        $this->assertDatabaseCount('covidcase', 2);
        $this->assertDatabaseHas('covidcase', ['uuid' => $caseOne->uuid]);
        $this->assertDatabaseHas('covidcase', ['uuid' => $caseThree->uuid]);
        $this->assertDatabaseMissing('covidcase', ['uuid' => $caseTwo->uuid]);
        $this->assertDatabaseMissing('covidcase', ['uuid' => $caseFour->uuid]);
    }

    public function testPurgingCaseWillPurgeChoresWithCallToAction(): void
    {
        $purgeDaysAgo = CarbonImmutable::now()->subDays(PurgeSoftDeletedModels::PURGE_AFTER_DAYS)->subDay();
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);

        $case = $this->createCaseForUser($user, ['deleted_at' => $purgeDaysAgo]);
        $callToAction = $this->createCallToAction();
        $chore = $this->createChoreForCaseAndOrganisation($case, $organisation, [
            'owner_resource_type' => 'call-to-action',
            'owner_resource_id' => $callToAction->uuid,
        ]);

        $this->artisan('models:purge')->execute();

        $this->assertDatabaseMissing('covidcase', ['uuid' => $case->uuid]);
        $this->assertDatabaseMissing('chore', ['uuid' => $chore->uuid]);
        $this->assertDatabaseMissing('call_to_action', ['uuid' => $callToAction->uuid]);
    }

    public function testAllForeignKeysToCaseHaveCascadeOption(): void
    {
        $foreignKeyRelationsWthoutProperDeleteRules = DB::connection()->select(
            "SELECT * FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS where UNIQUE_CONSTRAINT_SCHEMA='portal' and REFERENCED_TABLE_NAME='covidcase' and delete_rule='NO ACTION'",
        );
        $this->assertEquals([], array_map(static fn($row)=>$row->TABLE_NAME, $foreignKeyRelationsWthoutProperDeleteRules));
    }
}
