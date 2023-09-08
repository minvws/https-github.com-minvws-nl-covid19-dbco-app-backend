<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddOrganisationTypeAndOutsourceToggle extends Migration
{
    public function up(): void
    {
        Schema::table('organisation', static function (Blueprint $table): void {
            $table->string('type', 40)->after('uuid')->nullable();
            $table->tinyInteger('has_outsource_toggle')->default(0);
        });

        DB::table('organisation')->update(['type' => 'unknown']);

        Schema::table('organisation', static function (Blueprint $table): void {
            $table->string('type', 40)->change();
        });
    }

    public function down(): void
    {
        Schema::table('organisation', static function (Blueprint $table): void {
            $table->dropColumn('type');
            $table->dropColumn('has_outsource_toggle');
        });
    }
}
