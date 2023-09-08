<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemarksToContext extends Migration
{
    public function up(): void
    {
        Schema::table('context', static function (Blueprint $table): void {
            $table->text('remarks')->nullable()->after('detailed_explanation');
        });
    }

    public function down(): void
    {
        Schema::table('context', static function (Blueprint $table): void {
            $table->dropColumn('remarks');
        });
    }
}
