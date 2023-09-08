<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixNoteEncryption extends Migration
{
    public function up(): void
    {
        Schema::table('note', static function (Blueprint $table): void {
            $table->timestamp('case_created_at')->after('updated_at')->nullable(false);
            $table->dropColumn('notable_type');
            $table->renameColumn('notable_id', 'case_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('note', static function (Blueprint $table): void {
            $table->dropColumn('case_created_at');
            $table->string('notable_type');
            $table->renameColumn('case_uuid', 'notable_id');
        });
    }
}
