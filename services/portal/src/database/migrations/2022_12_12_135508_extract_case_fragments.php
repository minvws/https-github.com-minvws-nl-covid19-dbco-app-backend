<?php

declare(strict_types=1);

use App\Services\FragmentMigration\FragmentExtractionHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $this->upFragment('alternate_contact');
        $this->upFragment('edu_daycare');
        $this->upFragment('hospital');
        $this->upFragment('job');
        $this->upFragment('recent_birth');
        $this->upFragment('risk_location');
        $this->upFragment('source_environments');
    }

    public function down(): void
    {
        $this->downFragment('alternate_contact', 'AlternateContact');
        $this->downFragment('edu_daycare', 'EduDaycare');
        $this->downFragment('hospital', 'Hospital');
        $this->downFragment('job', 'Job');
        $this->downFragment('recent_birth', 'RecentBirth');
        $this->downFragment('risk_location', 'RiskLocation');
        $this->downFragment('source_environments', 'SourceEnvironments');
    }

    private function upFragment(string $column): void
    {
        FragmentExtractionHelper::extractCovidCaseFragment($column, 'fragment_name');

        DB::statement(sprintf('ALTER TABLE `covidcase` DROP COLUMN %s', $column));
    }

    private function downFragment(string $column, string $fragmentName): void
    {
        DB::statement(sprintf('ALTER TABLE `covidcase` ADD `%s` MEDIUMTEXT', $column));
        DB::statement(
            sprintf(
                "UPDATE `covidcase` SET `%s` = (SELECT CAST(cf.data AS CHAR ASCII) FROM `case_fragment` cf WHERE cf.case_uuid = `covidcase`.uuid AND cf.fragment_name = '%s')",
                $column,
                $fragmentName,
            ),
        );
        DB::statement(sprintf("DELETE FROM `case_fragment` WHERE fragment_name = '%s'", $fragmentName));
    }
};
