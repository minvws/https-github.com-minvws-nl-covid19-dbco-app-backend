<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class SeedContactMessageAttachment extends Migration
{
    public function up(): void
    {
        DB::statement("
            INSERT INTO attachment (uuid, file_name, created_at, updated_at)
            VALUES
                ('" . Uuid::uuid4() . "', '20211007_Bijlage_contacten_vaccinatie_en_COVID-19_GGDGHOR_nl.pdf', now(), created_at);
        ");
    }

    public function down(): void
    {
        DB::statement("
            DELETE FROM attachment WHERE file_name IN (
                '20211007_Bijlage_contacten_vaccinatie_en_COVID-19_GGDGHOR_nl.pdf'
            );");
    }
}
