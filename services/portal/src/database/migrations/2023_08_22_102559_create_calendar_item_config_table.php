<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_item_config', static function (Blueprint $table): void {
            $table->uuid()->primary();
            $table
                ->foreignUuid('policy_guideline_uuid')
                ->references('uuid')
                ->on('policy_guideline')
                ->cascadeOnDelete();
            $table
                ->foreignUuid('calendar_item_uuid')
                ->references('uuid')
                ->on('calendar_item')
                ->cascadeOnDelete();
            $table->boolean('is_hidden')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_item_config');
    }
};
