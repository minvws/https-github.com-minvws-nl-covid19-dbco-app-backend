<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoteTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('note', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuidMorphs('notable');
            $table->uuid('user_uuid')->nullable();
            $table->string('type')->nullable();
            $table->text('note');
            $table->timestamps();

            $table->foreign('user_uuid')->references('uuid')
                ->on('bcouser')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note');
    }
}
