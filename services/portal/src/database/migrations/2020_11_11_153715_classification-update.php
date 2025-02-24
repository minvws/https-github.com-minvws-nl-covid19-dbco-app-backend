<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ClassificationUpdate extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('answer', static function (Blueprint $table): void {
            $table->renameColumn('cfd_livedtogetherrisk', 'cfd_cat_1_risk');
            $table->renameColumn('cfd_durationrisk', 'cfd_cat_2a_risk');
            $table->renameColumn('cfd_distancerisk', 'cfd_cat_2b_risk');
            $table->renameColumn('cfd_otherrisk', 'cfd_cat_3_risk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('answer', static function (Blueprint $table): void {
            $table->renameColumn('cfd_cat_1_risk', 'cfd_livedtogetherrisk');
            $table->renameColumn('cfd_cat_2a_risk', 'cfd_durationrisk');
            $table->renameColumn('cfd_cat_2b_risk', 'cfd_distancerisk');
            $table->renameColumn('cfd_cat_3_risk', 'cfd_otherrisk');
        });
    }
}
