<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Generator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function array_shift;
use function explode;
use function implode;
use function is_array;
use function json_encode;
use function str_repeat;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

class DbCollectStatsCommand extends Command
{
    /** @var string */
    protected $signature = 'db:collect-stats';

    /** @var string */
    protected $description = 'Collect statistics about database tables and the partition of certain data';

    private function queries(): Generator
    {
        yield "Cases by owner organisation" => "
            SELECT o.name AS org_name, o.type AS org_type, o.external_id AS org_external_id, COUNT(*) AS `cases`
            FROM covidcase c
            JOIN organisation o ON (o.uuid = c.organisation_uuid)
            GROUP BY o.type, o.external_id, o.name
            ORDER BY o.type, o.external_id, o.name
        ";

        yield "Cases by assigned organisation" => "
            SELECT o.name AS org_name, o.type AS org_type, o.external_id AS org_external_id, COUNT(*) AS `cases`
            FROM covidcase c
            JOIN organisation o ON (o.uuid = c.assigned_organisation_uuid)
            GROUP BY o.type, o.external_id, o.name
            ORDER BY o.type, o.external_id, o.name
        ";

        yield "Cases by assigned case list" => "
            SELECT o.name AS org_name, o.type AS org_type, o.external_id AS org_external_id, cl.name AS case_list_name, COUNT(*) AS `cases`
            FROM covidcase c
            JOIN case_list cl ON (cl.uuid = c.assigned_case_list_uuid)
            JOIN organisation o ON (o.uuid = cl.organisation_uuid)
            GROUP BY o.type, o.external_id, o.name, cl.name
            ORDER BY o.type, o.external_id, o.name, cl.name
        ";

        yield "Cases assigned to users by organisation" => "
            SELECT o.name AS org_name, o.type AS org_type, o.external_id AS org_external_id, COUNT(*) AS `cases`
            FROM covidcase c
            JOIN organisation o ON (o.uuid = c.current_organisation_uuid)
            WHERE c.assigned_user_uuid IS NOT NULL
            GROUP BY o.name, o.type, o.external_id
        ";

        yield "Cases by year, week and BCO status" => "
            SELECT EXTRACT(YEAR FROM c.created_at) AS `year`, EXTRACT(WEEK FROM c.created_at) AS `week`, c.bco_status, COUNT(*) AS `cases`
            FROM covidcase c
            GROUP BY EXTRACT(YEAR FROM c.created_at), EXTRACT(WEEK FROM c.created_at), c.bco_status
            ORDER BY EXTRACT(YEAR FROM c.created_at), EXTRACT(WEEK FROM c.created_at), c.bco_status
        ";

        yield "Contacts by year, week" => "
            SELECT EXTRACT(YEAR FROM t.created_at) AS `year`, EXTRACT(WEEK FROM t.created_at) AS `week`, COUNT(*) AS `contacts`
            FROM task t
            GROUP BY EXTRACT(YEAR FROM t.created_at), EXTRACT(WEEK FROM t.created_at)
            ORDER BY EXTRACT(YEAR FROM t.created_at), EXTRACT(WEEK FROM t.created_at)
        ";

        yield "Average row length / table size per table" => [
            "SELECT
                 table_name AS `table_name`,
                 avg_row_length AS `avg_row_length`,
                 CONCAT(ROUND((data_length + index_length ) / 1024 / 1024, 2), ' MB') AS `table_size`
             FROM information_schema.tables
             WHERE table_schema = ?
             ORDER BY table_name",
            DB::connection()->getDatabaseName(),
        ];
    }

    public function handle(): int
    {
        echo "[";

        $isFirstQuery = true;
        foreach ($this->queries() as $description => $query) {
            if ($isFirstQuery) {
                $isFirstQuery = false;
            } else {
                echo ",";
            }

            $params = [];
            if (is_array($query)) {
                $params = $query;
                $query = array_shift($params);
            }

            echo "\n";
            echo str_repeat(' ', 4) . "{\n";
            echo str_repeat(' ', 8) . '"description": ' . json_encode($description) . ",\n";
            echo str_repeat(' ', 8) . "\"items\": [";

            $isFirstRow = true;
            foreach (DB::select($query, $params) as $row) {
                if ($isFirstRow) {
                    $isFirstRow = false;
                } else {
                    echo ",";
                }

                echo "\n";
                echo str_repeat(' ', 12) . implode(
                    "\n" . str_repeat(' ', 12),
                    explode(
                        "\n",
                        (string) json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                    ),
                );
            }

            echo "\n";
            echo str_repeat(' ', 8) . "]\n";
            echo str_repeat(' ', 4) . "}";
        }

        echo "\n";
        echo "]\n";

        return Command::SUCCESS;
    }
}
