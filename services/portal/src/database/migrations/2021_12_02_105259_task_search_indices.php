<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TaskSearchIndices extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE task
                ADD INDEX i_task_search_date_of_birth (search_date_of_birth),
                ADD INDEX i_task_search_email (search_email),
                ADD INDEX i_task_search_phone (search_phone)
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE task
                DROP INDEX i_task_search_date_of_birth,
                DROP INDEX i_task_search_email,
                DROP INDEX i_task_search_phone
        ");
    }
}
