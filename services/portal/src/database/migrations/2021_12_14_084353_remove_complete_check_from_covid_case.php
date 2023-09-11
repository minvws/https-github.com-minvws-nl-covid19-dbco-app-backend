<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCompleteCheckFromCovidCase extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('covidcase', 'complete_check')) {
            return;
        }

        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('complete_check');
        });
    }

    public function down(): void
    {
    }
}
