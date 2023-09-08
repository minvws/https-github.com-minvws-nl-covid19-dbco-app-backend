<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event', static function (Blueprint $table): void {
            $table->string('organisation_uuid', 36)->nullable();
        });

        DB::statement($this->createTrigger());
    }

    public function down(): void
    {
        Schema::table('event', static function (Blueprint $table): void {
            $table->dropColumn('organisation_uuid');
        });

        DB::statement($this->dropTrigger());
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
