<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risk_profile', static function (Blueprint $table): void {
            $table->dropForeign('risk_profile_policy_version_uuid_foreign');
            $table->foreign('policy_version_uuid')->references('uuid')->on('policy_version')->cascadeOnDelete();

            $table->dropForeign('risk_profile_policy_guideline_uuid_foreign');
            $table->foreign('policy_guideline_uuid')->references('uuid')->on('policy_guideline')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('risk_profile', static function (Blueprint $table): void {
            $table->dropForeign('risk_profile_policy_version_uuid_foreign');
            $table->foreign('policy_version_uuid')->references('uuid')->on('policy_version');

            $table->dropForeign('risk_profile_policy_guideline_uuid_foreign');
            $table->foreign('policy_guideline_uuid')->references('uuid')->on('policy_guideline');
        });
    }
};
