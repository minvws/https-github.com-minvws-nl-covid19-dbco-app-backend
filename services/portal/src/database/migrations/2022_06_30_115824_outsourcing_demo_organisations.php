<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

class OutsourcingDemoOrganisations extends Migration
{
    public function up(): void
    {
        Artisan::call('migration:update-organisation-outsourcing-7');
    }

    public function down(): void
    {
        // No.
    }
}
