<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('call_to_action_note', static function (Blueprint $table): void {
            $table->dropForeign('call_to_action_note_call_to_action_uuid_foreign');

            $table->foreign('call_to_action_uuid')->references('uuid')->on('call_to_action')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_to_action_note', static function (Blueprint $table): void {
            $table->dropForeign('call_to_action_note_call_to_action_uuid_foreign');

            $table->foreign('call_to_action_uuid')->references('uuid')->on('call_to_action');
        });
    }
};
