<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPseudoBsn extends Migration
{
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->string('pseudo_bsn_guid')->nullable();
            $table->index('pseudo_bsn_guid');
        });

        Schema::table('task', static function (Blueprint $table): void {
            $table->string('pseudo_bsn_guid')->nullable();
            $table->index('pseudo_bsn_guid');
        });
    }

    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('pseudo_bsn_guid');
        });

        Schema::table('task', static function (Blueprint $table): void {
            $table->dropColumn('pseudo_bsn_guid');
        });
    }
}
