<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HelperColumnForCopypaste extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('task', function (Blueprint $table) {
            $table->dateTime('copied_at')->nullable();
        });

        Schema::table('covidcase', function (Blueprint $table) {
            $table->string('export_id')->nullable();
            $table->dateTime('exported_at')->nullable();
            $table->dateTime('copied_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('covidcase', function (Blueprint $table) {
            $table->dropColumn('copied_at');
            $table->dropColumn('exported_at');
            $table->dropColumn('export_id');
        });

        Schema::table('task', function (Blueprint $table) {
            $table->dropColumn('copied_at');
        });
    }
}
