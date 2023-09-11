<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeEduDaycareName extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->renameColumn('edudaycare', 'edu_daycare');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->renameColumn('edu_daycare', 'edudaycare');
        });
    }
}
