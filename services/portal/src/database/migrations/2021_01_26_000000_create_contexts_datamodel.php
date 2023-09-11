<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContextsDatamodel extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('code')->nullable();
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);

            $table->index('sort_order');
        });

        Schema::create('place', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('label');
            $table->foreignUuid('category_uuid')
                ->nullable()
                ->references('uuid')
                ->on('category')
                ->onDelete('set null');
            $table->string('street')->nullable();
            $table->string('housenumber')->nullable();
            $table->string('housenumber_suffix')->nullable();
            $table->string('postalcode', 6)->nullable();
            $table->string('town')->nullable();
            $table->string('country', 2)->default('NL');
            $table->timestamps();

            $table->index('postalcode');
            $table->index(['postalcode', 'housenumber']);
        });

        Schema::create('place_reference', static function (Blueprint $table): void {
            $table->foreignUuid('place_uuid')
                ->references('uuid')
                ->on('place')
                ->cascadeOnDelete();
            $table->string('system');
            $table->string('external_id');
            $table->timestamps();

            $table->unique(['system', 'external_id']);
            $table->index('place_uuid');
        });

        Schema::create('section', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('label');
            $table->foreignUuid('place_uuid')
                ->references('uuid')
                ->on('place')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->index('place_uuid');
        });

        Schema::create('relationship', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);

            $table->index('sort_order');
        });

        Schema::create('context', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('label');
            $table->foreignUuid('covidcase_uuid')
                ->references('uuid')
                ->on('covidcase');
            $table->foreignUuid('place_uuid')
                ->nullable()
                ->references('uuid')
                ->on('place');
            $table->foreignUuid('relationship_uuid')
                ->nullable()
                ->references('uuid')
                ->on('relationship');
            $table->text('explanation')->nullable();
            $table->text('detailed_explanation')->nullable();
            $table->timestamps();

            $table->index('covidcase_uuid');
            $table->index('place_uuid');
        });

        Schema::create('context_section', static function (Blueprint $table): void {
            $table->foreignUuid('context_uuid')
                ->references('uuid')
                ->on('context')
                ->cascadeOnDelete();
            $table->foreignUuid('section_uuid')
                ->references('uuid')
                ->on('section')
                ->cascadeOnDelete();

            $table->index(['context_uuid', 'section_uuid']);
        });

        Schema::create('moment', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('context_uuid')
                ->references('uuid')
                ->on('context')
                ->cascadeOnDelete();
            $table->date('day');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->index('context_uuid');
            $table->index('day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moment');
        Schema::dropIfExists('context_section');
        Schema::dropIfExists('context');
        Schema::dropIfExists('place_reference');
        Schema::dropIfExists('section');
        Schema::dropIfExists('place');
        Schema::dropIfExists('category');
        Schema::dropIfExists('relationship');
    }
}
