<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropPlaceReferenceTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('place_reference');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('place_reference', static function (Blueprint $table): void {
            $table->index('place_uuid');
            $table->string('system');
            $table->string('external_id');
            $table->unique(['system', 'external_id']);
            $table->timestamps();

            $table->foreign('place_uuid')
                ->references('uuid')
                ->on('place')
                ->cascadeOnDelete();
        });
    }
}
