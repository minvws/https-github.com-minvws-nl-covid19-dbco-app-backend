<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlaceCategoryAsString extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('place', static function (Blueprint $table): void {
            $table->string('category')->nullable()->after('category_uuid');
        });

        DB::update("
            UPDATE place
            SET category = (SELECT c.code FROM category c WHERE c.uuid = place.category_uuid LIMIT 1)
        ");

        Schema::table('place', static function (Blueprint $table): void {
            $table->dropForeign(['category_uuid']);
            $table->dropColumn('category_uuid');
        });

        Schema::drop('category');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // not supported :/
    }
}
