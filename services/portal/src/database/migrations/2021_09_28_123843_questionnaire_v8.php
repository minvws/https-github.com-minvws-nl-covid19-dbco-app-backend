<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

class QuestionnaireV8 extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // install latest questionnaire
        Artisan::call('db:seed', ['--class' => 'QuestionnaireSeeder', '--force' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
}
