<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UniqueExternalIdAndType extends Migration
{
    public function up(): void
    {
        Schema::table('organisation', static function (Blueprint $table): void {
            $table->dropUnique(['external_id']);
            $table->unique(['external_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('organisation', static function (Blueprint $table): void {
            $table->dropUnique(['external_id', 'type']);
            $table->unique(['type']);
        });
    }
}
