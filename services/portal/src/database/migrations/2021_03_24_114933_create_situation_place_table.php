<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSituationPlaceTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('situation_place', static function (Blueprint $table): void {
            $table->uuid('place_uuid');
            $table->uuid('situation_uuid');
            $table->primary(['place_uuid', 'situation_uuid']);

            $table->foreign('place_uuid')->references('uuid')
                ->on('place')
                ->onDelete('cascade');

            $table->foreign('situation_uuid')->references('uuid')
                ->on('situation')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('situation_place');
    }
}
