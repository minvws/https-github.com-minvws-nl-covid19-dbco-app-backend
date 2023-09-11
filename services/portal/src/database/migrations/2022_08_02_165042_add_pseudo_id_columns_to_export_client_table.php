<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPseudoIdColumnsToExportClientTable extends Migration
{
    public function up(): void
    {
        Schema::table('export_client', static function (Blueprint $table): void {
            $table->string('pseudo_id_key_pair', 200);
            $table->string('pseudo_id_nonce', 200);
        });
    }

    public function down(): void
    {
        Schema::table('export_client', static function (Blueprint $table): void {
            $table->dropColumn('pseudo_id_key_pair');
            $table->dropColumn('pseudo_id_nonce');
        });
    }
}
