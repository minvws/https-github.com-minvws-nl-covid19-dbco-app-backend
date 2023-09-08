<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement($this->dropTrigger());
    }

    public function down(): void
    {
        DB::statement($this->createTrigger());
    }

    private function createTrigger(): string
    {
        return <<<SQL
            CREATE TRIGGER `trg_event_case_organisation_lookup_bi`
                BEFORE INSERT ON event
                FOR EACH ROW
                BEGIN
                    IF NEW.organisation_uuid IS NULL
                    THEN
                        SET NEW.organisation_uuid =
                            (
                                SELECT organisation_uuid
                                FROM covidcase
                                WHERE covidcase.uuid = json_extract(NEW.data, '$.caseUuid')
                            );
                    END IF;
                END
            SQL;
    }

    private function dropTrigger(): string
    {
        return "DROP TRIGGER IF EXISTS `trg_event_case_organisation_lookup_bi`";
    }
};
