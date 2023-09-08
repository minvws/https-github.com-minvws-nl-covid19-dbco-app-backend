<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreIndexSubmittedFieldsToCovidcase extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            // temporary column for storing the date of symptom onset the index submitted using the app
            // until we have a questionnaire at the index level
            $table->text('index_submitted_date_of_symptom_onset')->nullable();
            $table->text('index_submitted_date_of_test')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('index_submitted_date_of_symptom_onset', 'index_submitted_date_of_test');
        });
    }
}
