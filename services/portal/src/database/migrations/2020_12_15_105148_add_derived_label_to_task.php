<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDerivedLabelToTask extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->string('label')->nullable(true)->change();
            $table->string('derived_label')->nullable(true)->after('label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->dropColumn('derived_label');
            $table->string('label')->nullable(false)->change();
        });
    }
}
