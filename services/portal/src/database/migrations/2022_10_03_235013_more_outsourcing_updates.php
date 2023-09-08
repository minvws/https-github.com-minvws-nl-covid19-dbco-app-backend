<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MoreOutsourcingUpdates extends Migration
{
    private const UPDATES = [
        '10003' => ['11003', '12003', '13003', '14003', '25003'],
        '11003' => ['10003', '12003', '13003', '14003', '25003'],
        '12003' => ['10003', '11003', '13003', '14003', '25003'],
        '13003' => ['10003', '11003', '12003', '14003', '25003'],
        '14003' => ['10003', '11003', '12003', '13003', '25003'],
        '25003' => ['10003', '11003', '12003', '13003', '14003'],
        '01003' => ['02003', '03003'],
        '02003' => ['01003', '03003'],
        '03003' => ['01003', '02003'],
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

            // The toggle is enabled on the outsourcing organisation to reduce the number of database queries. It will
            // work as expected because all outsourcing organisations will also be outsourced to after this migration.
            DB::statement("UPDATE organisation SET has_outsource_toggle = 1 WHERE external_id = '$id'");
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
