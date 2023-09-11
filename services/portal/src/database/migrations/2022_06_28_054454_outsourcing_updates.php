<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class OutsourcingUpdates extends Migration
{
    private const UPDATES = [
        '04003' => ['05003', '06003', '07003', '08003', '09003'],
        '05003' => ['04003', '06003', '07003', '08003', '09003'],
        '06003' => ['04003', '05003', '07003', '08003', '09003'],
        '07003' => ['04003', '05003', '06003', '08003', '09003'],
        '08003' => ['04003', '05003', '06003', '07003', '09003'],
        '09003' => ['04003', '05003', '06003', '07003', '08003'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        foreach (self::UPDATES as $id => $outsourcingIds) {
            foreach ($outsourcingIds as $outsourcingId) {
                DB::statement("
                    REPLACE INTO organisation_outsource (organisation_uuid, outsources_to_organisation_uuid) VALUES (
                        (SELECT uuid FROM organisation WHERE external_id = '$id'),
                        (SELECT uuid FROM organisation WHERE external_id = '$outsourcingId')
                    );
                ");
            }

            DB::statement("UPDATE organisation SET is_available_for_outsourcing = 1 WHERE external_id = '$id'");
        }

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No.
    }
}
