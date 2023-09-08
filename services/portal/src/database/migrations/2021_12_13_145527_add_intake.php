<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIntake extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('intake', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('organisation_uuid')->nullable(false);
            $table->string('type', 50)->nullable(false);
            $table->string('source', 50)->nullable(false);
            $table->string('identifier_type', 50)->nullable(false);
            $table->string('identifier', 100)->nullable(false);
            $table->string('pseudo_bsn_guid', 100)->nullable(false);
            $table->mediumInteger('cat1_count')->nullable(true);
            $table->mediumInteger('estimated_cat2_count')->nullable(true);
            $table->dateTime('date_of_birth')->nullable(false);
            $table->datetime('date_of_symptom_onset')->nullable();
            $table->datetime('date_of_test')->nullable(false);
            $table->dateTime('received_at')->nullable(false);
            $table->dateTime('created_at')->nullable(false);
            $table->foreign('organisation_uuid')->references('uuid')->on('organisation');
            $table->softDeletes();
        });

        Schema::create('intake_fragment', static function (Blueprint $table): void {
            $table->uuid('intake_uuid')->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->mediumText('data')->nullable(false);
            $table->tinyInteger('version')->nullable(false);
            $table->primary(['intake_uuid', 'name'], 'intake_fragment_primary');
            $table->foreign('intake_uuid')->references('uuid')->on('intake')->cascadeOnDelete();
        });

        Schema::create('intake_label', static function (Blueprint $table): void {
            $table->uuid('intake_uuid')->nullable(false);
            $table->uuid('label_uuid')->nullable(false);
            $table->primary(['intake_uuid', 'label_uuid']);
            $table->foreign('intake_uuid')->references('uuid')->on('intake')->cascadeOnDelete();
            $table->foreign('label_uuid')->references('uuid')->on('case_label')->cascadeOnDelete();
        });

        Schema::create('intake_contact', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('intake_uuid')->nullable(false);
            $table->foreign('intake_uuid')->references('uuid')->on('intake')->cascadeOnDelete();
        });

        Schema::create('intake_contact_fragment', static function (Blueprint $table): void {
            $table->uuid('intake_contact_uuid')->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->mediumText('data')->nullable(false);
            $table->tinyInteger('version')->nullable(false);
            $table->primary(['intake_contact_uuid', 'name'], 'intake_contact_fragment_primary');
            $table->foreign('intake_contact_uuid')->references('uuid')->on('intake_contact')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('intake_contact_fragment');
        Schema::drop('intake_contact');
        Schema::drop('intake_label');
        Schema::drop('intake_fragment');
        Schema::drop('intake');
    }
}
