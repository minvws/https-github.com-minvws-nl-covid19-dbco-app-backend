<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoreCaseFragmentColumns extends Migration
{
    private const COLUMNS = [
        'symptoms',
        'test',
        'vaccination',
        'hospital',
        'underlying_suffering',
        'pregnancy',
        'recent_birth',
        'medication',
        'immunocompromised',
        'general_practitioner',
        'alternate_residency',
        'housemates',
        'risk_location',
        'job',
        'edudaycare',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            foreach (self::COLUMNS as $column) {
                $table->longText($column)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn(self::COLUMNS);
        });
    }
}
