<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            QuestionnaireSeeder::class,
            OrganizationSeeder::class

        ]);

        if (App::environment() == 'development') {
            // In development, also populate some test data
            $this->call([
                DummySeeder::class
            ]);
        }
    }
}
