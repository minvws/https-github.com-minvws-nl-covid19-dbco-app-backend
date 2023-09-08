<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $mapping = [
        'SARS-CoV-2 Zelftest' => 'selftest',
        'SARS-CoV-2 PCR' => 'pcr',
        'SARS-CoV-2 Antigeen' => 'antigen',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $numberOfIncompatibleRecords = DB::table('test_result')
            ->whereNotNull('type_of_test')
            ->whereNotIn('type_of_test', array_merge(array_keys($this->mapping)))
            ->count();
        if ($numberOfIncompatibleRecords > 0) {
            throw new Exception("There are $numberOfIncompatibleRecords records that have incompatible values. Migration is not possible");
        }
        Schema::table('test_result', function (Blueprint $table): void {
            foreach ($this->mapping as $old => $new) {
                DB::table('test_result')
                    ->where('type_of_test', '=', $old)
                    ->update(['type_of_test' => $new]);
            }
            DB::table('test_result')
                ->whereNull('type_of_test')
                ->update(['type_of_test' => 'unknown']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_result', function (Blueprint $table): void {
            foreach ($this->mapping as $old => $new) {
                DB::table('test_result')
                    ->where('type_of_test', '=', $new)
                    ->update(['type_of_test' => $old]);
            }
            DB::table('test_result')
                ->where('type_of_test', '=', 'unknown')
                ->update(['type_of_test' => null]);
        });
    }
};
