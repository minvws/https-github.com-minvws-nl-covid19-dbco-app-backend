<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPersonTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE TABLE `person`
            (
                `id` INT NOT NULL AUTO_INCREMENT,
                `uuid` BINARY(16) NOT NULL,
                `schema_version` INT NOT NULL,
                `date_of_birth` DATETIME NOT NULL,
                `pseudo_bsn_guid` VARCHAR(100),
                `search_non_bsn` VARCHAR(64),
                `search_date_of_birth` VARCHAR(64) NOT NULL,
                `search_email` VARCHAR(64),
                `search_phone` VARCHAR(64),
                `created_at` TIMESTAMP NOT NULL,
                `updated_at` TIMESTAMP NOT NULL,
                `deleted_at` TIMESTAMP,
                PRIMARY KEY (`id`),
                CONSTRAINT `u_person_u` UNIQUE (`uuid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        DB::statement("
            CREATE TABLE `person_fragment`
            (
                `person_id` INT NOT NULL,
                `name` VARCHAR(50) NOT NULL,
                `data` BLOB NOT NULL,
                `schema_version` INT NOT NULL,
                `created_at` TIMESTAMP NOT NULL,
                `updated_at` TIMESTAMP NOT NULL,
                `expires_at` TIMESTAMP NOT NULL,
                PRIMARY KEY (`person_id`, `name`),
                CONSTRAINT `fk_person_fragment_p` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE person_fragment');
        DB::statement('DROP TABLE person');
    }
}
