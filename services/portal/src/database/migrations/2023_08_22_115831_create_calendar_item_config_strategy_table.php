<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_item_config_strategy', static function (Blueprint $table): void {
            $table->uuid()->primary();
            $table
                ->foreignUuid('calendar_item_config_uuid')
                ->references('uuid')
                ->on('calendar_item_config')
                ->cascadeOnDelete();
            $table->string('strategy_loader_type');
            $table->string('identifier_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_item_config_strategy');
    }
};
