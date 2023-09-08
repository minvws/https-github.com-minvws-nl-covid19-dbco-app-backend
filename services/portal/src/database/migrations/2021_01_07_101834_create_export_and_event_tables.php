<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExportAndEventTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('export', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('status');
            $table->timestamp('created_at');
            $table->string('filename')->nullable();
            $table->timestamp('exported_at')->nullable();
            $table->timestamp('uploaded_at')->nullable();
        });

        Schema::create('event', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('type');
            $table->json('data');
            $table->json('export_data');
            $table->uuid('export_uuid')->nullable();
            $table->foreign('export_uuid')->references('uuid')
                ->on('export')
                ->nullOnDelete();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event');
        Schema::dropIfExists('export');
    }
}
