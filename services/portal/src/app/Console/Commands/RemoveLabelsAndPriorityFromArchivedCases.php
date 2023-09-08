<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Repositories\CaseRepository;
use Illuminate\Console\Command;
use MinVWS\DBCO\Enum\Models\Priority;

use function sprintf;

class RemoveLabelsAndPriorityFromArchivedCases extends Command
{
    /** @var string $signature */
    protected $signature = 'cases:remove-label-and-priority-from-archived';

    /** @var string $description */
    protected $description = 'Remove labels and priority for archived cases';

    public function handle(
        CaseRepository $caseRepository,
    ): int {
        $this->info('Removing labels and priority from archived cases...');

        $archivedCasesWithLabelsOrPriority = $caseRepository->getArchivedWithLabelsOrPriority();

        foreach ($archivedCasesWithLabelsOrPriority as $case) {
            $case->priority = Priority::none();
            $case->caseLabels()->detach();

            $caseRepository->save($case);
        }

        $this->info(
            sprintf('Removed labels and priority from %s cases', $archivedCasesWithLabelsOrPriority->count()),
        );

        return 0;
    }
}
