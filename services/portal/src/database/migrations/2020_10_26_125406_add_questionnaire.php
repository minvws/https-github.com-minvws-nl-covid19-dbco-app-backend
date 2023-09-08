<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuestionnaire extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questionnaire', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('name');
            $table->string('task_type');
            $table->integer('version'); // should be integer but oci8 laravel driver would give 'invalid variable name for bind'
            $table->timestamps();
        });

        Schema::create('question', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();

            $table->uuid('questionnaire_uuid');
            $table->foreign('questionnaire_uuid')->references('uuid')
                ->on('questionnaire')
                ->onDelete('cascade');

            $table->string('group');
            $table->string('question_type');
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('relevant_for_categories'); // comma separated 1,2a,2b etc
            $table->timestamps();
        });

        Schema::create('answer_option', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();

            $table->uuid('question_uuid');
            $table->foreign('question_uuid')->references('uuid')
                ->on('question')
                ->onDelete('cascade');

            $table->string('label');
            $table->string('value');
            $table->string('trigger')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answer_option');
        Schema::dropIfExists('question');
        Schema::dropIfExists('questionnaire');
    }
}
