<?php

declare(strict_types=1);

use App\Models\Eloquent\EloquentUser;
use Database\Seeders\DummySeeder;
use Illuminate\Database\Migrations\Migration;

class ChangeRoleToLandelijk extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $user = EloquentUser::find(DummySeeder::DEMO_OUTSOURCE_USER_UUID);
        if ($user instanceof EloquentUser && $user->roles === 'user') {
            $user->roles = 'user_nationwide';
            $user->save();
        }

        $planner = EloquentUser::find(DummySeeder::DEMO_OUTSOURCE_PLANNER_UUID);
        if (!($planner instanceof EloquentUser) || $planner->roles !== 'planner') {
            return;
        }

        $planner->roles = 'planner_nationwide';
        $planner->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $user = EloquentUser::find(DummySeeder::DEMO_OUTSOURCE_USER_UUID);
        if ($user instanceof EloquentUser && $user->roles === 'user_nationwide') {
            $user->roles = 'user';
            $user->save();
        }

        $planner = EloquentUser::find(DummySeeder::DEMO_OUTSOURCE_PLANNER_UUID);
        if (!($planner instanceof EloquentUser) || $planner->roles !== 'planner_nationwide') {
            return;
        }

        $planner->roles = 'planner';
        $planner->save();
    }
}
