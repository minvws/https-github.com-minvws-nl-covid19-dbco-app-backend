<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class SeedMailAttachments extends Migration
{
    public function up(): void
    {
        DB::statement("
            INSERT INTO attachment (uuid, file_name, created_at, updated_at)
            VALUES
                ('" . Uuid::uuid4() . "', '20211007_Bijlage_index_vaccinatie_en_COVID-19_GGDGHOR_nl.pdf', now(), created_at),
                ('" . Uuid::uuid4() . "', '20220331_Bijlage_Onderzoek_GGDGHOR_nl.pdf', now(), created_at),
                ('" . Uuid::uuid4() . "', '20220419_Attachment_Contact_tracing_en.pdf', now(), created_at),
                ('" . Uuid::uuid4() . "', '20220419_Bijlage_Contactinventarisatie_BCO_nl.pdf', now(), created_at);
        ");
    }

    public function down(): void
    {
        DB::statement("
            DELETE FROM attachment WHERE file_name IN (
                '20211007_Bijlage_index_vaccinatie_en_COVID-19_GGDGHOR_nl.pdf',
                '20220331_Bijlage_Onderzoek_GGDGHOR_nl.pdf',
                '20220419_Attachment_Contact_tracing_en.pdf',
                '20220419_Bijlage_Contactinventarisatie_BCO_nl.pdf'
            );");
    }
}
