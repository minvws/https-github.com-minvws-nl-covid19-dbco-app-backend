<?php

declare(strict_types=1);

use App\Models\StatusIndexContactTracing;
use Illuminate\Database\Migrations\Migration;

class UnassignCompletedCases extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('covidcase')
            ->where(static function ($query): void {
                $query->where('status_index_contact_tracing', StatusIndexContactTracing::CLOSED_OUTSIDE_GGD()->value)
                    ->orwhere('status_index_contact_tracing', StatusIndexContactTracing::CLOSED_NO_COLLABORATION()->value)
                    ->orwhere('status_index_contact_tracing', StatusIndexContactTracing::COMPLETED()->value);
            })
            ->update(['assigned_user_uuid' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
}
