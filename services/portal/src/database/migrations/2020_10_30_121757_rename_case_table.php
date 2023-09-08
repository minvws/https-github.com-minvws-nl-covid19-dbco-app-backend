<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameCaseTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('case', 'covidcase');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('covidcase', 'case');
    }
}
