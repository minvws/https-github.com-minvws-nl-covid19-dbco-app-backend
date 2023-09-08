<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserColumnsNoteTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('note', static function (Blueprint $table): void {
            $table->string('user_name')->after('user_uuid');
            $table->string('organisation_name')->nullable()->after('user_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('note', static function (Blueprint $table): void {
            $table->dropColumn('user_name');
            $table->dropColumn('organisation_name');
        });
    }
}
