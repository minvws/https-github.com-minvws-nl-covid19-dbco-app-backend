<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('date_operation', static function (Blueprint $table): void {
            $table->uuid()->primary();
            $table->foreignUuid('calendar_item_config_strategy_uuid')
                ->references('uuid')
                ->on('calendar_item_config_strategy')
                ->cascadeOnDelete();
            $table->string('identifier_type');
            $table->string('mutation_type');
            $table->unsignedInteger('amount');
            $table->string('unit_of_time_type');
            $table->string('origin_date_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('date_operation');
    }
};
