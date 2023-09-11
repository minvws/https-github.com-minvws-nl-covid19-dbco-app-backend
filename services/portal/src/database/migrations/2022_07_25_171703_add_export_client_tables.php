<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddExportClientTables extends Migration
{
    public function up(): void
    {
        Schema::create('export_client', static function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('x509_subject_dn_common_name', 64); // see https://www.rfc-editor.org/rfc/rfc5280 ub-common-name-length
        });

        Schema::create('export_client_organisation', static function (Blueprint $table): void {
            $table->unsignedBigInteger('export_client_id');
            $table->uuid('organisation_uuid');
            $table->primary(['export_client_id', 'organisation_uuid'], 'export_client_organisation_primary');
            $table->foreign('export_client_id')->references('id')
                ->on('export_client')
                ->cascadeOnDelete();
            $table->foreign('organisation_uuid')->references('uuid')
                ->on('organisation')
                ->cascadeOnDelete();
        });

        Schema::create('export_client_purpose', static function (Blueprint $table): void {
            $table->unsignedBigInteger('export_client_id');
            $table->string('purpose', 100);
            $table->primary(['export_client_id', 'purpose']);
            $table->foreign('export_client_id')->references('id')
                ->on('export_client')
                ->cascadeOnDelete();
        });

        DB::statement("
            ALTER TABLE export_client_purpose ADD CONSTRAINT chk_export_client_purpose_purpose CHECK (
                purpose IN (
                    'epidemiologicalSurveillance',
                    'qualityOfCare',
                    'administrativeAdvice',
                    'operationalAdjustment',
                    'scientificResearch'
                )
            )
        ");
    }

    public function down(): void
    {
        Schema::drop('export_client_purpose');
        Schema::drop('export_client_organisation');
        Schema::drop('export_client');
    }
}
