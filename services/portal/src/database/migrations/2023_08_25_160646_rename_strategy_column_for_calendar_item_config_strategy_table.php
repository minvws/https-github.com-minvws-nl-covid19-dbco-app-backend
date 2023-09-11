<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_item_config_strategy', static function (Blueprint $table): void {
            $table->renameColumn('strategy_loader_type', 'strategy_type');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_item_config_strategy', static function (Blueprint $table): void {
            $table->renameColumn('strategy_type', 'strategy_loader_type');
        });
    }
};
