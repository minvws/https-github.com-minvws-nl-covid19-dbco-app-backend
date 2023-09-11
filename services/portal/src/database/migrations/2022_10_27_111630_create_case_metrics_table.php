<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_metrics', static function (Blueprint $table): void {
            $table->id();
            $table->timestamp('date');
            $table->uuid('organisation_uuid');
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('archived_count')->default(0);
            $table->timestamp('refreshed_at');

            $table->foreign('organisation_uuid')->references('uuid')->on('organisation')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_metrics');
    }
};
