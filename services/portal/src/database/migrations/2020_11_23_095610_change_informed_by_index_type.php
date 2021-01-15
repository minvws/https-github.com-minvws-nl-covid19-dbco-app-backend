<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeInformedByIndexType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // can't change type without db layer complaining,
        // as we aren't storing critical data yet, just drop and re-create

        Schema::table('task', function (Blueprint $table) {
            $table->dropColumn('informed_by_index');
        });

        Schema::table('task', function (Blueprint $table) {
            $table->integer('informed_by_index')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // can't change type without db layer complaining,
        // as we aren't storing critical data yet, just drop and re-create

        Schema::table('task', function (Blueprint $table) {
            $table->dropColumn('informed_by_index');
        });

        Schema::table('task', function (Blueprint $table) {
            $table->boolean('informed_by_index')->default(false);
        });
    }
}
