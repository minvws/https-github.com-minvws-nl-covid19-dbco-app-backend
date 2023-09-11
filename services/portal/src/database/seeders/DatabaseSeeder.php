<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Helpers\Environment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            QuestionnaireSeeder::class,
            OrganisationSeeder::class,
            CaseLabelSeeder::class,
        ]);

        if (Environment::isDevelopment() || Environment::isTesting()) {
            // In development, also populate some test data
            $this->call([
                DummySeeder::class,
            ]);
        }

        $this->call([
            CaseLabelSeeder::class,
        ]);
    }
}
