<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('case_uuid');

            $table->foreign('case_uuid')->references('uuid')
                                                ->on('case')
                                                ->onDelete('cascade');
            $table->string('task_type');
            $table->string('source');
            $table->string('label');
            $table->string('task_context')->nullable();
            $table->string('nature')->nullable();
            $table->string('category')->nullable();
            $table->date('date_of_last_exposure')->nullable();
            $table->string('communication')->nullable();
            $table->boolean('informed_by_index');
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
        Schema::dropIfExists('task');
    }
}
