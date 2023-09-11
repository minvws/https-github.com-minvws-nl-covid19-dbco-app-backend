<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTestResultTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE TABLE `test_result`
            (
                `id` INT NOT NULL AUTO_INCREMENT,
                `uuid` BINARY(16) NOT NULL,
                `schema_version` INT NOT NULL,
                `organisation_uuid` CHAR(36) NOT NULL,
                `person_id` INT NOT NULL,
                `case_uuid` CHAR(36),
                `is_confirmed_for_case` TINYINT(1) NOT NULL DEFAULT 0,
                `type` VARCHAR(20) NOT NULL,
                `source` VARCHAR(40) NOT NULL,
                `monster_number` VARCHAR(40),
                `date_of_test` DATETIME NOT NULL,
                `date_of_symptom_onset` DATETIME,
                `received_at` TIMESTAMP NOT NULL,
                `created_at` TIMESTAMP NOT NULL,
                `updated_at` TIMESTAMP NOT NULL,
                `deleted_at` TIMESTAMP,
                PRIMARY KEY (`id`),
                CONSTRAINT `u_test_result_u` UNIQUE (`uuid`),
                CONSTRAINT `fk_test_result_o` FOREIGN KEY (`organisation_uuid`) REFERENCES `organisation` (`uuid`) ON DELETE RESTRICT,
                CONSTRAINT `fk_test_result_p` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`) ON DELETE RESTRICT,
                CONSTRAINT `fk_test_result_c` FOREIGN KEY (`case_uuid`) REFERENCES `covidcase` (`uuid`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        DB::statement("
            CREATE TABLE `test_result_fragment`
            (
                `test_result_id` INT NOT NULL,
                `name` VARCHAR(50) NOT NULL,
                `data` BLOB NOT NULL,
                `schema_version` INT NOT NULL,
                `created_at` TIMESTAMP NOT NULL,
                `updated_at` TIMESTAMP NOT NULL,
                `expires_at` TIMESTAMP NOT NULL,
                PRIMARY KEY (`test_result_id`, `name`),
                CONSTRAINT `fk_test_result_fragment_tr` FOREIGN KEY (`test_result_id`) REFERENCES `test_result` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        DB::statement("
            CREATE TABLE `test_result_label`
            (
                `test_result_id` INT NOT NULL,
                `label_uuid` CHAR(36) NOT NULL,
                PRIMARY KEY (`test_result_id`, `label_uuid`),
                CONSTRAINT `fk_test_result_label_tr` FOREIGN KEY (`test_result_id`) REFERENCES `test_result` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_test_result_label_l` FOREIGN KEY (`label_uuid`) REFERENCES `case_label` (`uuid`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE test_result_label');
        DB::statement('DROP TABLE test_result_fragment');
        DB::statement('DROP TABLE test_result');
    }
}
