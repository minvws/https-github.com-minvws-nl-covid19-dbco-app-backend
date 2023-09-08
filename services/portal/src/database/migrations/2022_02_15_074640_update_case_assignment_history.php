<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateCaseAssignmentHistory extends Migration
{
    public function up(): void
    {
        Schema::table('case_list', static function (Blueprint $table): void {
            $table->softDeletes();
        });

        Schema::table('case_assignment_history', static function (Blueprint $table): void {
            $table->string('assigned_case_list_name', 50)->nullable();
        });

        // When a list or user is deleted, don't delete the records from the case history
        DB::statement('ALTER TABLE case_assignment_history
            drop foreign key case_assignment_history_assigned_case_list_uuid_foreign,
            drop foreign key case_assignment_history_assigned_user_uuid_foreign
        ');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE case_assignment_history
            add constraint case_assignment_history_assigned_case_list_uuid_foreign
            foreign key (assigned_case_list_uuid) references case_list (uuid)
                on delete cascade,

                add constraint case_assignment_history_assigned_user_uuid_foreign
            foreign key (assigned_user_uuid) references bcouser (uuid)
                on delete cascade
        ');

        Schema::table('case_assignment_history', static function (Blueprint $table): void {
            $table->dropColumn('assigned_case_list_name');
        });

        Schema::table('case_list', static function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
}
