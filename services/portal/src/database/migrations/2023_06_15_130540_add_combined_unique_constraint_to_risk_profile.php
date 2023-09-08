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
        Schema::table('risk_profile', static function (Blueprint $table): void {
            $table->unique(['risk_profile_enum', 'policy_version_uuid'], 'risk_profile_risk_profile_enum_policy_version_uuid_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_profile', static function (Blueprint $table): void {
            $table->dropUnique('risk_profile_risk_profile_enum_policy_version_uuid_unique');
        });
    }
};
