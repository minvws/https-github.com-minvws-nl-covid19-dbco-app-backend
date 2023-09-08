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
        Schema::table('policy_variant', static function (Blueprint $table): void {
            $table->unique(['identifier', 'policy_version_uuid'], 'policy_variant_identifier_policy_version_uuid_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_variant', static function (Blueprint $table): void {
            $table->dropUnique('policy_variant_identifier_policy_version_uuid_unique');
        });
    }
};
