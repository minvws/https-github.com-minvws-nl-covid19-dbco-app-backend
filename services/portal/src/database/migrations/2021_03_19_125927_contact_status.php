<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ContactStatus extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->string('status_index_contact_tracing', 50)->nullable();
            $table->string('status_contacts_informed', 50)->nullable();
            $table->string('status_explanation', 500)->default('');
            $table->string('remarks_rivm', 500)->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn(['status_index_contact_tracing', 'status_contacts_informed', 'status_explanation', 'remarks_rivm']);
        });
    }
}
