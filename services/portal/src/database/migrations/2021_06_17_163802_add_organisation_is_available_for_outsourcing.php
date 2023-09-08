<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrganisationIsAvailableForOutsourcing extends Migration
{
    public function up(): void
    {
        Schema::table('organisation', static function (Blueprint $table): void {
            $table->tinyInteger('is_available_for_outsourcing')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('organisation', static function (Blueprint $table): void {
            $table->dropColumn('is_available_for_outsourcing');
        });
    }
}
