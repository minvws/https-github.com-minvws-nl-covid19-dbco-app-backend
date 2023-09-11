<?php

declare(strict_types=1);

use App\Services\FragmentMigration\FragmentExtractionHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        FragmentExtractionHelper::extractCovidCaseFragment('housemates', 'fragment_name');
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('housemates');
        });

        FragmentExtractionHelper::extractCovidCaseFragment('communication', 'fragment_name');
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('communication');
        });

        FragmentExtractionHelper::extractCovidCaseFragment('alternative_language', 'fragment_name');
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('alternative_language');
        });

        FragmentExtractionHelper::extractCovidCaseFragment('alternate_residency', 'fragment_name');
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('alternate_residency');
        });
    }

    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->longText('housemates')->nullable();
        });
        FragmentExtractionHelper::restoreFragmentData('Housemates', 'housemates');

        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->longText('communication')->nullable();
        });
        FragmentExtractionHelper::restoreFragmentData('Communication', 'communication');

        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->longText('alternative_language')->nullable();
        });
        FragmentExtractionHelper::restoreFragmentData('AlternativeLanguage', 'alternative_language');

        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->longText('alternate_residency')->nullable();
        });
        FragmentExtractionHelper::restoreFragmentData('AlternateResidency', 'alternate_residency');
    }
};
