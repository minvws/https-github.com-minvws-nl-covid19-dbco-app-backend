<?php

declare(strict_types=1);

use App\Services\FragmentMigration\FragmentExtractionHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ExtractCaseIndexFragment extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        FragmentExtractionHelper::extractCovidCaseFragment('index');
        DB::statement("ALTER TABLE covidcase DROP COLUMN `index`");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE covidcase ADD `index` MEDIUMTEXT");
        DB::statement(
            "UPDATE covidcase SET `index` = (SELECT CAST(cf.data AS CHAR ASCII) FROM case_fragment cf WHERE cf.case_uuid = covidcase.uuid AND cf.name = 'Index')",
        );
        DB::statement("DELETE FROM `case_fragment` WHERE name = 'Index'");
    }
}
