<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionAndCreatedByColumnsToCallToActionTable extends Migration
{
    public function up(): void
    {
        Schema::table('call_to_action', static function (Blueprint $table): void {
            $table->text('description')->after('subject')->nullable(); // Make nullable to make sure migration does not fail
            $table->uuid('created_by')->after('description')->nullable(); // Make nullable to make sure migration does not fail

            $table->foreign('created_by')->references('uuid')->on('bcouser');
        });
    }

    public function down(): void
    {
        Schema::table('call_to_action', static function (Blueprint $table): void {
            $table->dropForeign('created_by');

            $table->dropColumn('description');
            $table->dropColumn('created_by');
        });
    }
}
