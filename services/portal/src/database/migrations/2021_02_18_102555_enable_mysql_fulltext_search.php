<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnableMysqlFulltextSearch extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE place ADD FULLTEXT INDEX place_fulltext (label, street, postalcode)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('place', static function ($table): void {
            $table->dropIndex('place_fulltext');
        });
    }
}
