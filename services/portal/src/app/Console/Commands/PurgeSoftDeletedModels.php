<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SoftDeletedModelsService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

use function sprintf;
use function trans_choice;

class PurgeSoftDeletedModels extends Command
{
    public const PURGE_AFTER_DAYS = 7;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'models:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge soft deleted models after X days';

    /**
     * Execute the console command.
     */
    public function handle(SoftDeletedModelsService $softDeletedModelsService): int
    {
        $date = CarbonImmutable::now()->subDays(self::PURGE_AFTER_DAYS);

        $this->info('Purging soft deleted models...');

        $casesDeleted = $softDeletedModelsService->purgeCasesWithChoresBeforeDate($date);
        $this->infoPurgedModels($casesDeleted, 'covidcase|covidcases');

        $tasksDeleted = $softDeletedModelsService->purgeTasksBeforeDate($date);
        $this->infoPurgedModels($tasksDeleted, 'task|tasks');

        return 0;
    }

    private function infoPurgedModels(int $count, string $modelTranslation): void
    {
        $this->info(sprintf('Purged %d %s', $count, trans_choice($modelTranslation, $count)));
    }
}
