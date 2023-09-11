<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('policy_versions', static function (Blueprint $table): void {
            $table->uuid()->primary();
            $table->string('name');
            $table->string('status');
            $table->dateTime('started_at');
            $table->timestamps();
        });

        Schema::table('risk_profile', static function (Blueprint $table): void {
            $table->uuid('policy_version_uuid')->after('risk_profile_enum');
            $table->foreign('policy_version_uuid')->references('uuid')->on('policy_versions');
        });

        Schema::table('policy_variant', static function (Blueprint $table): void {
            $table->uuid('policy_version_uuid')->after('identifier');
            $table->foreign('policy_version_uuid')->references('uuid')->on('policy_versions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_profile', static function (Blueprint $table): void {
            $table->dropColumn(['policy_version_uuid']);
        });

        Schema::table('policy_variant', static function (Blueprint $table): void {
            $table->dropColumn(['policy_version_uuid']);
        });

        Schema::dropIfExists('policy_versions');
    }
};
