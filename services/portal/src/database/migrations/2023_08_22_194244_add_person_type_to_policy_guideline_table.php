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
            $table->string('person_type')->after('policy_version_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('policy_guideline', static function (Blueprint $table): void {
            $table->dropColumn('person_type');
        });
    }
};
