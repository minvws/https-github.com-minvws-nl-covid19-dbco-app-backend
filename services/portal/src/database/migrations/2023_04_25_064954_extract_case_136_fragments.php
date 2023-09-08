<?php

declare(strict_types=1);

use App\Services\FragmentMigration\FragmentExtractionHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /*Extensive Contact Tracing*/
        FragmentExtractionHelper::extractCovidCaseFragment('extensive_contact_tracing', 'fragment_name');
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('extensive_contact_tracing');
        });

        /*Principal Contextual Settings*/
        FragmentExtractionHelper::extractContextFragment('principal_contextual_settings', 'fragment_name');
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('principal_contextual_settings');
        });
    }

    public function down(): void
    {
        /*Extensive Contact Tracing*/
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->longText('extensive_contact_tracing')->nullable();
        });

        FragmentExtractionHelper::restoreFragmentData('ExtensiveContactTracing', 'extensive_contact_tracing');

        /*Principal Contextual Settings*/
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->longText('principal_contextual_settings')->nullable();
        });

        FragmentExtractionHelper::restoreFragmentData('PrincipalContextualSettings', 'principal_contextual_settings');
    }
};
