<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_status_history', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('covidcase_uuid');
            $table->string('bco_status', 15)->index();
            $table->timestamp('changed_at')->index();

            $table->foreign('covidcase_uuid')->references('uuid')->on('covidcase')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_status_history');
    }
};
