<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('covidcase', function (Blueprint $table) {
            $table->date('pairing_expires_at')->nullable();
            $table->date('window_expires_at')->nullable();
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
            $table->dropColumn('pairing_expires_at');
            $table->dropColumn('window_expires_at');
        });
    }
}
