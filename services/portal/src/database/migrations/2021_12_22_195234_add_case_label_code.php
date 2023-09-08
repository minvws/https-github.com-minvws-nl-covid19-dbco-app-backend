<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCaseLabelCode extends Migration
{
    public function up(): void
    {
        Schema::table('case_label', static function (Blueprint $table): void {
            $table->string('code')->nullable()->unique()->after('uuid');
        });

        DB::table('case_label')->where('label', 'Zorg')->update(['code' => 'healthcare']);
        DB::table('case_label')->where('label', 'Bewoner zorg')->update(['code' => 'healthcare_residant']);
        DB::table('case_label')->where('label', 'Medewerker zorg')->update(['code' => 'healthcare_employee']);
        DB::table('case_label')->where('label', 'School')->update(['code' => 'school']);
        DB::table('case_label')->where('label', 'Contactberoep')->update(['code' => 'contact_profession']);
        DB::table('case_label')->where('label', 'Maatschappelijke instelling')->update(['code' => 'social_institution']);
        DB::table('case_label')->where('label', 'Scheepvaart opvarende')->update(['code' => 'shipping_person']);
        DB::table('case_label')->where('label', 'Vluchten')->update(['code' => 'flights']);
        DB::table('case_label')->where('label', 'Buitenland')->update(['code' => 'abroad']);
        DB::table('case_label')->where('label', 'VOI/VOC')->update(['code' => 'voi_voc']);
        DB::table('case_label')->where('label', 'Herhaaluitslag')->update(['code' => 'repeat_result']);
        DB::table('case_label')->where('label', 'Buiten meldportaal/CoronIT')->update(['code' => 'external']);
        DB::table('case_label')->where('label', 'Onvolledige gegevens')->update(['code' => 'incomplete_data']);
        DB::table('case_label')->where('label', 'Index weet uitslag niet')->update(['code' => 'index_unaware_result']);
        DB::table('case_label')->where('label', 'Uitbraak')->update(['code' => 'outbreak']);
        DB::table('case_label')->where('label', 'Steekproef')->update(['code' => 'sample']);
    }

    public function down(): void
    {
        Schema::table('case_label', static function (Blueprint $table): void {
            $table->dropColumn('code');
        });
    }
}
