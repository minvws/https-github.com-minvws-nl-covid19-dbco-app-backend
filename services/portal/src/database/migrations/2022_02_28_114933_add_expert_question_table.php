<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddExpertQuestionTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            create table `expert_question`
            (
                `uuid`            char(36)     not null,
                `created_at`      timestamp    null,
                `updated_at`      timestamp    null,
                `case_created_at` timestamp    not null,
                `case_uuid`       char(36)     not null,
                `user_uuid`       char(36)     not null,
                `type`            varchar(50)  not null,
                `subject`         varchar(255) not null,
                `phone`           varchar(255) null,
                `question`        text         not null
            ) default character set utf8mb4
              collate 'utf8mb4_unicode_ci';
        ");

        DB::statement("
            alter table `expert_question`
                add primary key `expert_question_uuid_primary` (`uuid`),
                add constraint `expert_question_case_uuid_foreign` foreign key (`case_uuid`) references `covidcase` (`uuid`),
                add constraint `expert_question_user_uuid_foreign` foreign key (`user_uuid`) references `bcouser` (`uuid`)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE expert_question');
    }
}
