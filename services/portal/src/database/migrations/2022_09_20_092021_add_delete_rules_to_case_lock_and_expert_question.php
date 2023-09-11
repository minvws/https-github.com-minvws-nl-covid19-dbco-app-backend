<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_lock', static function (Blueprint $table): void {
            $table->dropForeign(['case_uuid']);
            $table->foreign('case_uuid')
                ->references('uuid')
                ->on('covidcase')
                ->onDelete('cascade');
        });
        Schema::table('expert_question', static function (Blueprint $table): void {
            $table->dropForeign(['case_uuid']);
            $table->foreign('case_uuid')
                ->references('uuid')
                ->on('covidcase')
                ->onDelete('cascade');
        });
    }
};
