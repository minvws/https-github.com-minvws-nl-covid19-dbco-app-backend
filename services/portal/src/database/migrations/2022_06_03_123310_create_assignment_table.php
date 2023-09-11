<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignmentTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assignment', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('chore_uuid');
            $table->uuid('user_uuid');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('chore_uuid')->references('uuid')->on('chore');
            $table->foreign('user_uuid')->references('uuid')->on('bcouser');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment');
    }
}
