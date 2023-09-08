<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallToActionNote extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('call_to_action_note', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('user_uuid');
            $table->uuid('call_to_action_uuid');
            $table->text('note');
            $table->timestamps();

            $table->foreign('user_uuid')->references('uuid')->on('bcouser');
            $table->foreign('call_to_action_uuid')->references('uuid')->on('call_to_action');

            $table->index('call_to_action_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_to_action_note');
    }
}
