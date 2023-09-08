<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCaseUpdateContactColumns extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('case_update_contact', static function (Blueprint $table): void {
            $table->renameColumn('type', 'contact_group');
        });

        Schema::table('case_update_contact', static function (Blueprint $table): void {
            $table->string('label', 200)->after('contact_group')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_update_contact', static function (Blueprint $table): void {
            $table->dropColumn('label');
            $table->renameColumn('contact_group', 'type');
        });
    }
}
