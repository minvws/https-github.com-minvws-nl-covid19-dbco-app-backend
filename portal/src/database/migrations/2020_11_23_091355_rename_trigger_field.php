<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTriggerField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('answer_option', function (Blueprint $table) {
            $table->renameColumn('trigger', 'trigger_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('answer_option', function (Blueprint $table) {
            $table->renameColumn('trigger_name', 'trigger');
        });
    }
}
