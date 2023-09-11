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
        Schema::table('test_result', static function (Blueprint $table): void {
            $table->integer('person_id')->nullable()->change();
            $table->string('message_id', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_result', static function (Blueprint $table): void {
            $table->integer('person_id')->nullable(false)->change();
            $table->string('message_id', 50)->nullable(false)->change();
        });
    }
};
