<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTemplatesIsSecure extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mail_template', static function (Blueprint $table): void {
            $table->boolean('secure')->default(true);
        });

        DB::table('mail_template')
            ->where('type', '=', 'missed_phone')
            ->update([
                'secure' => false,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_template', static function (Blueprint $table): void {
            $table->dropColumn('secure');
        });
    }
}
