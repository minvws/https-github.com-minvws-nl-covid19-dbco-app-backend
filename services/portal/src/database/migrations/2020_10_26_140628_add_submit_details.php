<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubmitDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('case', function (Blueprint $table) {
            $table->datetime('index_submitted_at')->nullable();
            $table->datetime('seen_at')->nullable();
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
            $table->dropColumn('seen_at');
            $table->dropColumn('index_submitted_at');
        });
    }
}
