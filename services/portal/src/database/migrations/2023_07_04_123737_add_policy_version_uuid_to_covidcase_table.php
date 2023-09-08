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
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->uuid('policy_version_uuid')->nullable()->after('owner');

            $table->foreign('policy_version_uuid')->references('uuid')
                ->on('policy_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropForeign('covidcase_policy_version_uuid_foreign');
            $table->dropColumn('policy_version_uuid');
        });
    }
};
