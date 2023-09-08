<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSimilarSearchIndexToPlace extends Migration
{
    public function up(): void
    {
        DB::statement('
            ALTER TABLE place
                ADD INDEX i_place_similar (organisation_uuid, label, street, postalcode, town(100))
        ');
    }

    public function down(): void
    {
        // Index cannot be dropped as it is needed in a foreign key constraint
    }
}
