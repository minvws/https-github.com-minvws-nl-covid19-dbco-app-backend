<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('case_uuid');
            $table->foreign('case_uuid')->references('uuid')
                ->on('covidcase')
                ->onDelete('cascade');
            $table->string('token');
            $table->string('receive_key');
            $table->string('transmit_key');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client');
    }
}
