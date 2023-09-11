<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('policy_variant', 'policy_guideline');

        Schema::table('risk_profile', static function (Blueprint $table): void {
            $table->renameColumn('policy_variant_uuid', 'policy_guideline_uuid');
            $table->dropForeign('risk_profile_policy_variant_uuid_foreign');
            $table->foreign('policy_guideline_uuid')->references('uuid')->on('policy_guideline');
        });
    }

    public function down(): void
    {
        Schema::rename('policy_guideline', 'policy_variant');

        Schema::table('risk_profile', static function (Blueprint $table): void {
            $table->renameColumn('policy_guideline_uuid', 'policy_variant_uuid');
            $table->dropForeign('risk_profile_policy_guideline_uuid_foreign');
            $table->foreign('policy_variant_uuid')->references('uuid')->on('policy_variant');
        });
    }
};
