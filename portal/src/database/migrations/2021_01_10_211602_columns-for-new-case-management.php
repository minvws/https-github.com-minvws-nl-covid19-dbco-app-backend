<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ColumnsForNewCaseManagement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('covidcase', function (Blueprint $table) {
            $table->date('date_of_test')->nullable();
            $table->integer('symptomatic')->nullable();
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
            $table->dropColumn('symptomatic');
            $table->dropColumn('date_of_test');
        });
    }
}
