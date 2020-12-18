<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client', function (Blueprint $table) {
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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client');
    }
}
