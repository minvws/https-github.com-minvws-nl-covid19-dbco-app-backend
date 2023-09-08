<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpertUserUuidToExpertQuestionTable extends Migration
{
    public function up(): void
    {
        Schema::table('expert_question', static function (Blueprint $table): void {
            $table->string('expert_user_uuid')->nullable()->after('user_uuid');
            $table->foreign('expert_user_uuid')->references('uuid')->on('bcouser');
            $table->index('expert_user_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('expert_question', static function (Blueprint $table): void {
            $table->dropIndex('expert_user_uuid');
            $table->dropConstrainedForeignId('expert_user_uuid');
            $table->dropColumn('expert_user_uuid');
        });
    }
}
