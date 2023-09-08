<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_fragment', static function (Blueprint $table): void {
            $table->renameColumn('name', 'fragment_name');
        });
        Schema::table('person_fragment', static function (Blueprint $table): void {
            $table->renameColumn('name', 'fragment_name');
        });
        Schema::table('test_result_fragment', static function (Blueprint $table): void {
            $table->renameColumn('name', 'fragment_name');
        });
    }

    public function down(): void
    {
        Schema::table('case_fragment', static function (Blueprint $table): void {
            $table->renameColumn('fragment_name', 'name');
        });
        Schema::table('person_fragment', static function (Blueprint $table): void {
            $table->renameColumn('fragment_name', 'name');
        });
        Schema::table('test_result_fragment', static function (Blueprint $table): void {
            $table->renameColumn('fragment_name', 'name');
        });
    }
};
