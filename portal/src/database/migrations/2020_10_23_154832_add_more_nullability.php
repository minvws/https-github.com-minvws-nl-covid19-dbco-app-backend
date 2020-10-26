<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreNullability extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('case', function (Blueprint $table) {
            // Fields should be nullable because draft cases don't have data yet.
            // oci integration has an error when making columns nullable, so we cheat by
            // dropping the column and recreating it. We don't have any data yet at this point.
            $table->dropColumn('name');
            $table->dropColumn('case_id');
        });

        Schema::table('case', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('case_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('case', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('case_id');
        });

        Schema::table('case', function (Blueprint $table) {
            $table->string('name');
            $table->string('case_id');
        });
    }
}
