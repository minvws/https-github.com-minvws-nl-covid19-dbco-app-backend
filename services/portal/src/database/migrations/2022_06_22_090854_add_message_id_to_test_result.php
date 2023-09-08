<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMessageIdToTestResult extends Migration
{
    public function up(): void
    {
        /** @var stdClass $result */
        $result = DB::selectOne('SELECT EXISTS(SELECT 1 FROM test_result) as has_rows;');

        if (!$result->has_rows) {
            Schema::table('test_result', static function (Blueprint $table): void {
                $table->string('message_id', 50)
                    ->nullable(false)
                    ->unique('u_test_result_message_id');
            });
        } else {
            Schema::table('test_result', static function (Blueprint $table): void {
                $table->string('message_id', 50)->nullable(true);
            });
            DB::statement('update test_result SET `message_id` = `id` WHERE `message_id` is null;');
            DB::statement('alter table test_result modify message_id varchar(50) not null;');
            DB::statement('create unique index u_test_result_message_id on test_result (`message_id`);');
        }
    }

    public function down(): void
    {
        Schema::table('test_result', static function (Blueprint $table): void {
            $table->dropUnique('u_test_result_message_id');
            $table->dropColumn('message_id');
        });
    }
}
