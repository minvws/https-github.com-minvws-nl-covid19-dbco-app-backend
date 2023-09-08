<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSituationCaseTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('situation_case', static function (Blueprint $table): void {
            $table->uuid('case_uuid');
            $table->uuid('situation_uuid');
            $table->primary(['case_uuid', 'situation_uuid']);

            $table->foreign('case_uuid')->references('uuid')
                ->on('covidcase')
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
        Schema::dropIfExists('situation_case');
    }
}
