<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFragmentsToTask extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->longText('general')->nullable();
            $table->longText('circumstances')->nullable();
            $table->longText('symptoms')->nullable();
            $table->longText('test')->nullable();
            $table->longText('vaccination')->nullable();
            $table->longText('personal_details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->dropColumn(['general', 'circumstances', 'symptoms', 'test', 'vaccination', 'personal_details']);
        });
    }
}
