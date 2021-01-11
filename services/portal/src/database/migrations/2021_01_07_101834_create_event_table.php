<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('type');
            $table->string('actor');

            // case data
            $table->uuid('case_uuid')->nullable();
            $table->foreign('case_uuid')->references('uuid')
                ->on('covidcase')
                ->nullOnDelete();
            $table->string('case_pseudo_id');
            $table->string('case_organisation_external_id');
            $table->date('case_date_of_symptom_onset')->nullable();

            // task data
            $table->string('task_uuid')->nullable();
            $table->foreign('task_uuid')->references('uuid')
                ->on('task')
                ->nullOnDelete();
            $table->string('task_pseudo_id')->nullable();
            $table->string('task_category')->nullable();
            $table->tinyInteger('task_completeness')->nullable();
            $table->text('task_fields')->nullable();
            $table->string('task_export_id')->nullable();

            $table->timestamp('created_at');
            $table->timestamp('exported_at')->nullable();
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
    }
}
