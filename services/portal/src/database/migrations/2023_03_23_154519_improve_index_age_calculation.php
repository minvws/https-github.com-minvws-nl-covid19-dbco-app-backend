<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->index('bco_status', 'covidcase_bco_status_index');

            $table->tinyInteger('index_age_calculator_key')
                ->nullable()
                ->index('index_age_calcuator_key_index');
        });
    }

    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropIndex('covidcase_bco_status_index');
            $table->dropIndex('index_age_calcuator_key_index');

            $table->dropColumn('index_age_calculator_key');
        });
    }
};
