<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('person', static function (Blueprint $table): void {
            $table->string('search_date_of_birth')
                ->nullable()
                ->change();
            $table->longText('date_of_birth_encrypted')
                ->nullable()
                ->after('date_of_birth');
        });
    }

    public function down(): void
    {
        Schema::table('person', static function (Blueprint $table): void {
            $table->dropColumn(['date_of_birth_encrypted']);
            $table->string('search_date_of_birth')
                ->nullable(false)
                ->change();
        });
    }
};
