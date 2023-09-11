<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCaseUpdate extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('case_update', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('case_uuid')->nullable(false);
            $table->string('source', 50)->nullable(false);
            $table->string('pseudo_bsn_guid', 100)->nullable();
            $table->dateTime('received_at')->nullable(false);
            $table->dateTime('created_at')->nullable(false);
            $table->dateTime('updated_at')->nullable(false);
            $table->foreign('case_uuid')->references('uuid')->on('covidcase')->cascadeOnDelete();
        });

        Schema::create('case_update_fragment', static function (Blueprint $table): void {
            $table->uuid('case_update_uuid')->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->mediumText('data')->nullable(false);
            $table->tinyInteger('version')->nullable(false);
            $table->dateTime('received_at')->nullable(false);
            $table->dateTime('created_at')->nullable(false);
            $table->dateTime('updated_at')->nullable(false);
            $table->primary(['case_update_uuid', 'name'], 'case_update_fragment_primary');
            $table->foreign('case_update_uuid')->references('uuid')->on('case_update')->cascadeOnDelete();
        });

        Schema::create('case_update_contact', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('case_update_uuid')->nullable(false);
            $table->string('type', 20)->nullable(false);
            $table->uuid('contact_uuid')->nullable();
            $table->dateTime('received_at')->nullable(false);
            $table->dateTime('created_at')->nullable(false);
            $table->dateTime('updated_at')->nullable(false);
            $table->foreign('case_update_uuid')->references('uuid')->on('case_update')->cascadeOnDelete();
            $table->foreign('contact_uuid')->references('uuid')->on('task')->nullOnDelete();
        });

        Schema::create('case_update_contact_fragment', static function (Blueprint $table): void {
            $table->uuid('case_update_contact_uuid')->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->mediumText('data')->nullable(false);
            $table->tinyInteger('version')->nullable(false);
            $table->dateTime('received_at')->nullable(false);
            $table->dateTime('created_at')->nullable(false);
            $table->dateTime('updated_at')->nullable(false);
            $table->primary(['case_update_contact_uuid', 'name'], 'case_update_contact_fragment_primary');
            $table->foreign('case_update_contact_uuid')->references('uuid')->on('case_update_contact')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('case_update_contact_fragment');
        Schema::drop('case_update_contact');
        Schema::drop('case_update_fragment');
        Schema::drop('case_update');
    }
}
