<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\ContextCategory;

class ResetPlaceCategory extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::update("
            UPDATE place
            SET category = :category
        ", [ContextCategory::onbekend()->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // not supported
    }
}
