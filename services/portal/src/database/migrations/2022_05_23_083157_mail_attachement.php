<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MailAttachement extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE `attachment`
            (
                uuid               CHAR(36)     NOT NULL,
                file_name           CHAR(250)    NOT NULL,
                created_at         DATETIME     NOT NULL,
                updated_at         DATETIME     NOT NULL,
                inactive_since     TIMESTAMP    DEFAULT NULL,
                PRIMARY KEY (uuid)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        DB::statement("
            CREATE TABLE `message_attachment`
            (
                message_uuid        CHAR(36)     NOT NULL,
                attachment_uuid     CHAR(36)     NOT NULL,
                PRIMARY KEY (message_uuid, attachment_uuid),

                constraint message_attachment_message_uuid_foreign
                    foreign key (message_uuid) references message (uuid)
                        on delete cascade,
                constraint message_attachment_attachment_uuid_foreign
                    foreign key (attachment_uuid) references attachment(uuid)
                        on delete cascade
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE attachment');
        DB::statement('DROP TABLE message_attachment');
    }
}
