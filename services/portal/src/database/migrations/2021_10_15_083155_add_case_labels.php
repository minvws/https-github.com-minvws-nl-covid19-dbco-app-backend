<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCaseLabels extends Migration
{
    public function up(): void
    {
        Schema::create('case_label', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('label');
            $table->timestamps();
        });

        Schema::create('case_case_label', static function (Blueprint $table): void {
            $table->foreignUuid('case_uuid')->references('uuid')->on('covidcase')->cascadeOnDelete();
            $table->foreignUuid('case_label_uuid')->references('uuid')->on('case_label')->cascadeOnDelete();

            $table->unique(['case_uuid', 'case_label_uuid']);
        });

        Schema::create('case_label_organisation', static function (Blueprint $table): void {
            $table->foreignUuid('case_label_uuid')->references('uuid')->on('case_label')->cascadeOnDelete();
            $table->foreignUuid('organisation_uuid')->references('uuid')->on('organisation')->cascadeOnDelete();
            $table->integer('sortorder')->default(0);

            $table->unique(['case_label_uuid', 'organisation_uuid']);
        });
    }

    public function down(): void
    {
        Schema::drop('case_label');
        Schema::drop('case_case_label');
        Schema::drop('case_label_organisation');
    }
}
