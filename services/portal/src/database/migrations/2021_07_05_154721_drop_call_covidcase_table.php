<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropCallCovidcaseTable extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('covidcase', 'call')) {
            Schema::table('covidcase', static function (Blueprint $table): void {
                $table->dropColumn('call');
            });
        }
    }

    public function down(): void
    {
        // No migration needed to roll back
    }
}
