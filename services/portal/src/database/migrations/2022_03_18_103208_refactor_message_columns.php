<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RefactorMessageColumns extends Migration
{
    public function up(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->dropColumn('smm_summary');
            $table->dropColumn('smm_preview');
            $table->dropColumn('smm_footer');
        });
    }

    public function down(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->text('smm_summary')->nullable();
            $table->text('smm_preview');
            $table->text('smm_footer');
        });
    }
}
