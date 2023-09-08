<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use function env;
use function sprintf;

/**
 * Used for load tests.
 */
class DummyAdditionalUsersSeeder extends Seeder
{
    /**
     * Run the dummy seed.
     */
    public function run(): void
    {
        $now = CarbonImmutable::now();
        $totalNormalUsers = env('DUMMY_ADDITIONAL_SEED_NORMAL_USERS', 20_000);
        $totalPlannerUsers = env('DUMMY_ADDITIONAL_SEED_PLANNER_USERS', 20_000);

        // skip 1 because that one is already created by the normal DummySeeder
        for ($i = 2; $i <= $totalNormalUsers; $i++) {
            $dummyUserUuid = sprintf('00000000-0000-0000-0000-1%011d', $i);
            $this->createUser($dummyUserUuid, 'Demo Gebruiker ' . $i, 'user', $now);
        }

        // skip 1 because that one is already created by the normal DummySeeder
        for ($i = 2; $i <= $totalPlannerUsers; $i++) {
            $dummyUserUuid = sprintf('00000000-0000-0000-0000-2%011d', $i);
            $this->createUser($dummyUserUuid, 'Demo Planner ' . $i, 'user,planner', $now);
        }
    }

    private function createUser(string $uuid, string $name, string $roles, CarbonInterface $now): void
    {
        DB::table('bcouser')->insert([
            'name' => $name,
            'uuid' => $uuid,
            'external_id' => $uuid,
            'roles' => $roles,
            'created_at' => $now,
            'updated_at' => $now,
            'consented_at' => null,
        ]);

        DB::table('user_organisation')->insert([
            'user_uuid' => $uuid,
            'organisation_uuid' => DummySeeder::DEMO_ORGANISATION_UUID,
            'created_at' => $now,
            'updated_at' => null,
        ]);
    }
}
