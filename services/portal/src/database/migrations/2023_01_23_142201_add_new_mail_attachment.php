<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            INSERT INTO attachment (uuid, file_name, created_at, updated_at)
            VALUES
                ('" . Uuid::uuid4() . "', '20230123_Bijlage_index_vaccinatie_en_COVID-19_GGDGHOR_nl.pdf', now(), created_at)
        ");

        DB::statement("
            UPDATE attachment
            SET attachment.inactive_since = now()
            WHERE attachment.file_name = '20211007_Bijlage_index_vaccinatie_en_COVID-19_GGDGHOR_nl.pdf'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            UPDATE attachment
            SET attachment.inactive_since = NULL
            WHERE attachment.file_name = '20211007_Bijlage_index_vaccinatie_en_COVID-19_GGDGHOR_nl.pdf'
        ");

        DB::statement("
            DELETE FROM attachment 
            WHERE attachment.file_name = '20230123_Bijlage_index_vaccinatie_en_COVID-19_GGDGHOR_nl.pdf'
        ");
    }
};
