<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_profile', static function (Blueprint $table): void {
            $table->uuid()->primary();
            $table->string('identifier');
            $table->string('name');
            $table->text('description')->nullable();

            $table->string('source_start_date_reference');
            $table->integer('source_start_date_addition');
            $table->string('source_end_date_reference');
            $table->integer('source_end_date_addition');

            $table->string('contagious_start_date_reference');
            $table->integer('contagious_start_date_addition');
            $table->string('contagious_end_date_reference');
            $table->integer('contagious_end_date_addition');

            $table->timestamps();
        });

        Schema::create('index_type', static function (Blueprint $table): void {
            $table->uuid()->primary();
            $table->string('name');
            $table->string('index_type_enum');
            $table->uuid('policy_profile_uuid');
            $table->text('description')->nullable();
            $table->boolean('is_active')->nullable()->default(false);
            $table->integer('sort_order');

            $table->timestamps();
            $table->foreign('policy_profile_uuid')->references('uuid')->on('policy_profile');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('index_type');
        Schema::dropIfExists('policy_profile');
    }
};
