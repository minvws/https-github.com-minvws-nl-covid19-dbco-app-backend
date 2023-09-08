<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCaseFragmentTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE TABLE `case_fragment`
            (
                `id` INT NOT NULL AUTO_INCREMENT,
                `case_uuid` CHAR(36) NOT NULL,
                `name` VARCHAR(50) NOT NULL,
                `data` TEXT CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `schema_version` INT NOT NULL,
                `created_at` TIMESTAMP NOT NULL,
                `updated_at` TIMESTAMP NOT NULL,
                `expires_at` TIMESTAMP NOT NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `u_case_fragment_cun` UNIQUE (`case_uuid`, `name`),
                CONSTRAINT `fk_case_fragment_c` FOREIGN KEY (`case_uuid`) REFERENCES `covidcase` (`uuid`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE `case_fragment`");
    }
}
