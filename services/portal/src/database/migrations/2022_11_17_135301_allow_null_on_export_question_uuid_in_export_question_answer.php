<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expert_question_answer', static function (Blueprint $table): void {
            $table->uuid('expert_question_uuid')->nullable()->change();
        });
    }

    public function down(): void
    {
    }
};
