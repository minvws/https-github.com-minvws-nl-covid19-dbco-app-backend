<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SplitUpCaseStatus extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->renameColumn('status', 'bco_status');
            $table->string('index_status')->after('status')->default('initial');
        });

        DB::table('covidcase')
            ->where('bco_status', 'open')
            ->where('pairing_expires_at', '<', DB::raw('NOW()'))
            ->update([
                'bco_status' => 'open',
                'index_status' => 'timeout',
            ]);

        DB::table('covidcase')
            ->where('bco_status', 'paired')
            ->whereNull('index_submitted_at')
            ->where('window_expires_at', '>', DB::raw('NOW()'))
            ->update([
                'bco_status' => 'open',
                'index_status' => 'paired',
            ]);

        DB::table('covidcase')
            ->where('bco_status', 'paired')
            ->whereNull('index_submitted_at')
            ->where('window_expires_at', '<', DB::raw('NOW()'))
            ->update([
                'bco_status' => 'open',
                'index_status' => 'expired',
            ]);

        DB::table('covidcase')
            ->where('bco_status', 'paired')
            ->whereNotNull('index_submitted_at')
            ->where('window_expires_at', '>', DB::raw('NOW()'))
            ->where(static function ($query): void {
                $query->select(DB::raw('count(*)'))
                    ->from('task')
                    ->where('task.case_uuid', '=', DB::raw('covidcase.uuid'))
                    ->whereNotNull('task.questionnaire_uuid');
            }, '>', 0)
            ->update([
                'bco_status' => 'open',
                'index_status' => 'delivered',
            ]);

        //Should not occur, just to be save
        DB::table('covidcase')
            ->whereIn('bco_status', ['paired', 'timeout', 'expired', 'delivered'])
            ->update([
                'index_status' => DB::raw('bco_status'),
            ]);
        DB::table('covidcase')
            ->whereIn('bco_status', ['paired', 'timeout', 'expired', 'delivered'])
            ->update([
                'bco_status' => 'open',
            ]);

        //Just to be save
        DB::table('covidcase')
            ->whereIn('bco_status', ['processed', 'exported'])
            ->update([
                'bco_status' => 'completed',
                'index_status' => 'delivered',
            ]);

        //Just to be save
        DB::table('covidcase')
            ->where('bco_status', 'archived')
            ->update([
                'bco_status' => 'completed',
                'index_status' => 'delivered',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->renameColumn('bco_status', 'status');
            $table->dropColumn('index_status');
        });
    }
}
