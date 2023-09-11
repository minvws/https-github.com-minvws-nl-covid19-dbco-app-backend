<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use Database\Seeders\DummySeeder;
use Illuminate\Console\Command;

class TestDataUpdateDemoUsersCommand extends Command
{
    /** @var string $signature */
    protected $signature = 'test-data:update-users';

    /** @var string $description */
    protected $description = 'Update demo-users';

    public function handle(): int
    {
        $this->info('Updating demo-users: start');

        $users = [
            [
                'uuid' => DummySeeder::DEMO_DATACATALOG,
                'name' => 'Demo GGD1 Datacatalogus',
                'roles' => 'datacatalog',
            ],
            [
                'uuid' => DummySeeder::DEMO_CLUSTERSPECIALIST_UUID,
                'name' => 'Demo GGD1 Clusterspecialist',
                'roles' => 'clusterspecialist',
            ],
            [
                'uuid' => DummySeeder::DEMO_USER_CLUSTERSPECIALIST_UUID,
                'name' => 'Demo GGD1 Gebruiker & Clusterspecialist',
                'roles' => 'user,clusterspecialist',
            ],
            [
                'uuid' => DummySeeder::DEMO_USER_PLANNER_CONTEXTMANAGER_CASEQUALITY_UUID,
                'name' => 'Demo GGD 1 Gebruiker & Werkverdeler & Contextbeheerder & Dossierkwaliteit',
                'roles' => 'user,planner,contextmanager,casequality',
            ],
            [
                'uuid' => DummySeeder::DEMO_USER_CALLCENTER_UUID,
                'name' => 'Demo GGD1 Gebruiker & Callcenter Basis',
                'roles' => 'user,callcenter',
            ],
            [
                'uuid' => DummySeeder::DEMO_CALLCENTER_EXPERT_UUID,
                'name' => 'Demo GGD1 Callcenter Expert',
                'roles' => 'callcenter_expert',
            ],
            [
                'uuid' => DummySeeder::DEMO_USER_CALLCENTER_EXPERT_UUID,
                'name' => 'Demo GGD1 Gebruiker & Callcenter Expert',
                'roles' => 'user,callcenter_expert',
            ],
            [
                'uuid' => DummySeeder::DEMO_USER_CASEQUALITY_UUID,
                'name' => 'Demo GGD1 Gebruiker & Dossierkwaliteit',
                'roles' => 'user,casequality',
            ],
            [
                'uuid' => DummySeeder::DEMO_USER_CASEQUALITY_PLANNER_UUID,
                'name' => 'Demo GGD1 Gebruiker & Dossierkwaliteit & Werkverdeler',
                'roles' => 'user,casequality,planner',
            ],
            [
                'uuid' => DummySeeder::DEMO_TWO_CLUSTERSPECIALIST_UUID,
                'name' => 'Demo GGD2 Clusterspecialist',
                'roles' => 'clusterspecialist',
            ],
            [
                'uuid' => DummySeeder::DEMO_TWO_CALLCENTER_EXPERT_UUID,
                'name' => 'Demo GGD2 Callcenter Expert',
                'roles' => 'callcenter_expert',
            ],
        ];

        /** @var EloquentOrganisation $organisationGgd1 */
        $organisationGgd1 = EloquentOrganisation::query()
            ->where('uuid', DummySeeder::DEMO_ORGANISATION_UUID)
            ->firstOrFail();

        $eloquentUserUuids = [];
        foreach ($users as $user) {
            $eloquentUser = EloquentUser::query()->updateOrCreate(
                [
                    'uuid' => $user['uuid'],
                ],
                [
                    'name' => $user['name'],
                    'roles' => $user['roles'],
                    'external_id' => $user['uuid'],
                ],
            );
            $eloquentUserUuids[] = $eloquentUser->uuid;
        }

        $organisationGgd1->users()->syncWithoutDetaching($eloquentUserUuids);

        $this->info('Updating demo-users: done');

        return self::SUCCESS;
    }
}
