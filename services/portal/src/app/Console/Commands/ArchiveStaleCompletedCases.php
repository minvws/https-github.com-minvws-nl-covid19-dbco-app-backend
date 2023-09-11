<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CaseArchiveService;
use Illuminate\Console\Command;

use function sprintf;

class ArchiveStaleCompletedCases extends Command
{
    /** @var string */
    protected $signature = 'cases:archive-stale-completed-cases';

    /** @var string */
    protected $description = 'Archive cases that are marked completed and stale for a configured period';

    public function handle(
        CaseArchiveService $caseArchiveService,
    ): int {
        $this->info('Archiving stale completed cases...');

        $archivedCount = $caseArchiveService->archiveStaleCompleted();

        $this->info(sprintf('Archived %s stale completed cases', $archivedCount));

        return Command::SUCCESS;
    }
}
