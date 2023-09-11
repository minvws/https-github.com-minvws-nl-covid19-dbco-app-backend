<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ModifyTestResultAndRelatedTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `person_fragment`
                ADD `id` INT NOT NULL AUTO_INCREMENT FIRST,
                DROP PRIMARY KEY,
                ADD PRIMARY KEY (`id`),
                ADD CONSTRAINT `u_person_fragment_pn` UNIQUE (`person_id`, `name`)
        ");

        DB::statement("
            ALTER TABLE `test_result_fragment`
                ADD `id` INT NOT NULL AUTO_INCREMENT FIRST,
                DROP PRIMARY KEY,
                ADD PRIMARY KEY (`id`),
                ADD CONSTRAINT `u_test_result_fragment_trn` UNIQUE (`test_result_id`, `name`)
        ");

        DB::statement("
            ALTER TABLE `test_result` ADD `source_id` VARCHAR(50) AFTER `source`
        ");

        DB::statement("
            CREATE TABLE `test_result_raw`
            (
                `test_result_id` INT NOT NULL,
                `schema_version` INT NOT NULL,
                `data` MEDIUMBLOB NOT NULL,
                PRIMARY KEY (`test_result_id`),
                CONSTRAINT `fk_test_result_raw_tr` FOREIGN KEY (`test_result_id`)
                    REFERENCES `test_result` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE `test_result_raw`");

        DB::statement("
            ALTER TABLE `test_result` DROP COLUMN `source_id`
        ");

        DB::statement("
            ALTER TABLE `test_result_fragment`
                DROP CONSTRAINT `u_test_result_fragment_trn`,
                DROP PRIMARY KEY,
                ADD PRIMARY KEY (`test_result_id`, `name`),
                DROP COLUMN `id`
        ");

        DB::statement("
            ALTER TABLE `person_fragment`
                DROP CONSTRAINT `u_person_fragment_pn`,
                DROP PRIMARY KEY,
                ADD PRIMARY KEY (`person_id`, `name`),
                DROP COLUMN `id`
        ");
    }
}
