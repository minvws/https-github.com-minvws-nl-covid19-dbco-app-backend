<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_item', static function (Blueprint $table): void {
            $table->renameColumn('fixed_calendar_item_name_enum', 'fixed_calendar_item_enum');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_item', static function (Blueprint $table): void {
            $table->renameColumn('fixed_calendar_item_enum', 'fixed_calendar_item_name_enum');
        });
    }
};
