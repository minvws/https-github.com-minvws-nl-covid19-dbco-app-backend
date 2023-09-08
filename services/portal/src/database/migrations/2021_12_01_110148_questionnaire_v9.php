<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

class QuestionnaireV9 extends Migration
{
    public function up(): void
    {
        // Install latest questionnaire
        Artisan::call('db:seed', ['--class' => 'QuestionnaireSeeder', '--force' => true]);
    }

    public function down(): void
    {
        // Do nothing
    }
}
