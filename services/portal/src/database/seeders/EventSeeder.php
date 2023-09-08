<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Eloquent\EloquentCase;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use MinVWS\Metrics\Models\Export;

use function json_encode;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = (new Factory())->create();
        $export = [];
        $events = [];

        //a loop that runs x times
        for ($i = 0; $i < 100; $i++) {
            $data = [
                'actor' => 'staff',
                'caseUuid' => EloquentCase::factory()->create()->uuid,
            ];

            $export[$i] = [
                'uuid' => Str::uuid(),
                'status' => Export::STATUS_EXPORTED,
                'created_at' => $faker->dateTimeBetween('-1 year'),
                'uploaded_at' => $faker->dateTimeBetween('-1 year'),
                'exported_at' => $faker->dateTimeBetween('-1 year'),
                'filename' => $faker->word(),
            ];

            $events[$i] = [
                'uuid' => Str::uuid(),
                'type' => $faker->word(),
                'data' => json_encode($data),
                'export_data' => json_encode($data),
                'export_uuid' => $export[$i]['uuid'],
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
            ];
        }


        DB::table('export')->insert($export);
        DB::table('event')->insert($events);
    }
}
