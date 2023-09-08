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
        Schema::create('place_counters', static function (Blueprint $table): void {
            $table->uuid('place_uuid')->primary();

            $table->integer('index_count')->default(0);
            $table->integer('index_count_since_reset')->default(0);
            $table->dateTime('last_index_presence')->nullable();

            $table->timestamps();

            $table->foreign('place_uuid')
                ->references('uuid')
                ->on('place')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('place_counters');
    }
};
