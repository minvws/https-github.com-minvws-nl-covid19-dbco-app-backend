<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChoreTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chore', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('organisation_uuid');
            $table->string('resource_type');
            $table->string('resource_id');
            $table->string('resource_permission');
            $table->string('owner_resource_type');
            $table->string('owner_resource_id');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organisation_uuid')->references('uuid')->on('organisation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chore');
    }
}
