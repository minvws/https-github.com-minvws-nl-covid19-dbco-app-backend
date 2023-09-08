<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFragmentsForImmunity extends Migration
{
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->longText('immunity')->nullable();
        });

        Schema::table('task', static function (Blueprint $table): void {
            $table->longText('immunity')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn([
                'immunity',
            ]);
        });

        Schema::table('task', static function (Blueprint $table): void {
            $table->dropColumn([
                'immunity',
            ]);
        });
    }
}
