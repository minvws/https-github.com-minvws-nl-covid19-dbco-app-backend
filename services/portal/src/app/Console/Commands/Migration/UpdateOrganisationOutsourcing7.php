<?php

declare(strict_types=1);

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateOrganisationOutsourcing7 extends Command
{
    private const UPDATES = [
        '00000' => ['demo-ggd2', 'demo-ls1', 'demo-ls2'],
        'demo-ggd2' => ['00000', 'demo-ls2'],
    ];

    protected $signature = 'migration:update-organisation-outsourcing-7';

    protected $description = 'Update the Demo outsourcing relations (DBCO-4850)';

    public function handle(): int
    {
        $this->handleUpdates();
        $this->getOutput()->success('Finished updating outsource mapping.');

        return Command::SUCCESS;
    }

    private function handleUpdates(): void
    {
        DB::beginTransaction();

        foreach (self::UPDATES as $id => $outsourcingIds) {
            foreach ($outsourcingIds as $outsourcingId) {
                DB::statement("
                    REPLACE INTO organisation_outsource (
                        SELECT o1.uuid, o2.uuid FROM organisation o1
                        INNER JOIN organisation o2
                        WHERE o1.external_id = '$id'
                        AND o2.external_id = '$outsourcingId'
                    );
                ");

                DB::statement("UPDATE organisation SET is_available_for_outsourcing = 1 WHERE external_id = '$outsourcingId'");
            }
        }

        DB::commit();
    }
}
