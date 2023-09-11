<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTriggerField extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('answer_option', static function (Blueprint $table): void {
            $table->renameColumn('trigger', 'trigger_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('answer_option', static function (Blueprint $table): void {
            $table->renameColumn('trigger_name', 'trigger');
        });
    }
}
