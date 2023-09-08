<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DefineCompositePkeyContextSections extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('context_section', static function (Blueprint $table): void {
            $table->primary(['context_uuid', 'section_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('context_section', static function (Blueprint $table): void {
            $table->dropPrimary(['context_uuid', 'section_uuid']);
        });
    }
}
