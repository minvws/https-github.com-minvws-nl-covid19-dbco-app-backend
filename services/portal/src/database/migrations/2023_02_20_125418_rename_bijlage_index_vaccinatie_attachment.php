<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            UPDATE attachment
            SET attachment.file_name = '20221004_Bijlage_Vaccineren_Index_en_COVID-19.pdf'
            WHERE attachment.file_name = '20230123_Bijlage_index_vaccinatie_en_COVID-19_GGDGHOR_nl.pdf'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            UPDATE attachment
            SET attachment.file_name = '20230123_Bijlage_index_vaccinatie_en_COVID-19_GGDGHOR_nl.pdf'
            WHERE attachment.file_name = '20221004_Bijlage_Vaccineren_Index_en_COVID-19.pdf'
        ");
    }
};
