<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('osiris_history', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('case_uuid')->index();
            $table->string('status');
            $table->string('osiris_status');
            $table->json('osiris_validation_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('osiris_history');
    }
};
