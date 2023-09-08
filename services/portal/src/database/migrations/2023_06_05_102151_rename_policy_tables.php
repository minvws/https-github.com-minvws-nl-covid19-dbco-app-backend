<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('index_type', 'risk_profile');
        Schema::rename('policy_profile', 'policy_variant');

        Schema::table('risk_profile', static function (Blueprint $table): void {
            $table->renameColumn('index_type_enum', 'risk_profile_enum');
            $table->renameColumn('policy_profile_uuid', 'policy_variant_uuid');
            $table->dropForeign('index_type_policy_profile_uuid_foreign');
            $table->foreign('policy_variant_uuid')->references('uuid')->on('policy_variant');
        });
    }

    public function down(): void
    {
        Schema::rename('risk_profile', 'index_type');
        Schema::rename('policy_variant', 'policy_profile');

        Schema::table('index_type', static function (Blueprint $table): void {
            $table->renameColumn('risk_profile_enum', 'index_type_enum');
            $table->renameColumn('policy_variant_uuid', 'policy_profile_uuid');
            $table->dropForeign('risk_profile_policy_variant_uuid_foreign');
            $table->foreign('policy_profile_uuid')->references('uuid')->on('policy_profile');
        });
    }
};
