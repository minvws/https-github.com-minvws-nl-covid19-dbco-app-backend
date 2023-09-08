<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCovidCaseCaseNumberAndIndexes extends Migration
{
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->string('case_number')->nullable()->after('case_id');

            $table->index('created_at');
            $table->index('updated_at');
            $table->index('date_of_test');
            $table->index('case_number');
            $table->index('status_index_contact_tracing');
            $table->index('status_contacts_informed');
        });
    }

    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropIndex('case_number');

            $table->dropColumn('case_number');
        });
    }
}
