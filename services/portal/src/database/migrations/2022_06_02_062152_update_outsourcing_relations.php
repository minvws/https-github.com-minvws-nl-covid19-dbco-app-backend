<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

class UpdateOutsourcingRelations extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Artisan::call('migration:update-organisation-outsourcing-6');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No.
    }
}
