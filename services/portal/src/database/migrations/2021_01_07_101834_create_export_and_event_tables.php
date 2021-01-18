<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExportAndEventTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('export', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('status');
            $table->timestamp('created_at');
            $table->string('filename')->nullable();
            $table->timestamp('exported_at')->nullable();
            $table->timestamp('uploaded_at')->nullable();
        });

        Schema::create('event', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('type');
            $table->json('data');
            $table->json('export_data');
            $table->uuid('export_uuid')->nullable();
            $table->foreign('export_uuid')->references('uuid')
                ->on('export')
                ->nullOnDelete();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event');
        Schema::dropIfExists('export');
    }
}
