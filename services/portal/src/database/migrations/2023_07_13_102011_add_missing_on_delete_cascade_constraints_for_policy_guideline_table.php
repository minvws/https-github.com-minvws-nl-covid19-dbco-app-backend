<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('policy_guideline', static function (Blueprint $table): void {
            $table->dropForeign('policy_variant_policy_version_uuid_foreign');
            $table->foreign('policy_version_uuid')->references('uuid')->on('policy_version')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('policy_guideline', static function (Blueprint $table): void {
            $table->dropForeign('policy_guideline_policy_version_uuid_foreign');
            $table->foreign('policy_version_uuid', 'policy_variant_policy_version_uuid_foreign')->references('uuid')->on('policy_version');
        });
    }
};
