<?php

declare(strict_types=1);

use App\Services\FragmentMigration\FragmentExtractionHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const COVIDCASE = 'covidcase';

    public function up(): void
    {
        FragmentExtractionHelper::extractCovidCaseFragment('abroad', 'fragment_name');
        Schema::table(self::COVIDCASE, static function (Blueprint $table): void {
            $table->dropColumn('abroad');
        });

        FragmentExtractionHelper::extractCovidCaseFragment('contact', 'fragment_name');
        Schema::table(self::COVIDCASE, static function (Blueprint $table): void {
            $table->dropColumn('contact');
        });

        FragmentExtractionHelper::extractCovidCaseFragment('contacts', 'fragment_name');
        Schema::table(self::COVIDCASE, static function (Blueprint $table): void {
            $table->dropColumn('contacts');
        });

        FragmentExtractionHelper::extractCovidCaseFragment('deceased', 'fragment_name');
        Schema::table(self::COVIDCASE, static function (Blueprint $table): void {
            $table->dropColumn('deceased');
        });
    }

    public function down(): void
    {
        Schema::table(self::COVIDCASE, static function (Blueprint $table): void {
            $table->longText('abroad')->nullable();
        });

        FragmentExtractionHelper::restoreFragmentData('Abroad', 'abroad');

        Schema::table(self::COVIDCASE, static function (Blueprint $table): void {
            $table->longText('contact')->nullable();
        });

        FragmentExtractionHelper::restoreFragmentData('Contact', 'contact');

        Schema::table(self::COVIDCASE, static function (Blueprint $table): void {
            $table->longText('contacts')->nullable();
        });

        FragmentExtractionHelper::restoreFragmentData('Contacts', 'contacts');

        Schema::table(self::COVIDCASE, static function (Blueprint $table): void {
            $table->longText('deceased')->nullable();
        });

        FragmentExtractionHelper::restoreFragmentData('Deceased', 'deceased');
    }
};
