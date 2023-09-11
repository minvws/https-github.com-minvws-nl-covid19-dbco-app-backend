<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\BCOPhase;
use Symfony\Component\Console\Helper\ProgressBar;
use Throwable;

use function is_string;

class BcoPhaseReplace extends Command
{
    protected $signature = 'bco-phase:replace
        {--to=    : To which bco phase does it need to change (Default to BCO Phase 1)}
        {--from=  : Determine which bco phases need to change (Default to all cases)}
        {--chunk= : Set chunk count}
        {--force  : Forge the command to set trough if validation fails}';

    protected $description = 'Reset all BCO-Phase fields';

    protected const CHUNK_COUNT = 1000;

    protected string $to;
    protected ?string $from;
    protected int $chunkCount;
    protected bool $force;

    public function handle(): int
    {
        if ($this->setVariables()) {
            return Command::FAILURE;
        }

        try {
            $this->resetBcoPhaseOnOrganisations();
            $this->resetBcoPhaseOnCases();
        } catch (Throwable $exception) {
            $this->getOutput()->error("Exception has occurred: {$exception->getMessage()}");

            return Command::FAILURE;
        }

        $this->getOutput()->success('Finished resetting all BCO Phase fields');

        return Command::SUCCESS;
    }

    protected function setVariables(): ?bool
    {
        $this->force = (bool) $this->option('force');

        if (!$this->force) {
            if ($this->validateOptions()) {
                return true;
            }
        }

        $this->to = is_string($this->option('to')) ? (string) $this->option('to') : BCOPhase::phase1()->value;
        $this->from = is_string($this->option('from')) ? (string) $this->option('from') : null;
        $this->chunkCount = (int) ($this->option('chunk') ?? self::CHUNK_COUNT);

        return null;
    }

    protected function validateOptions(): bool
    {
        if (!$this->option('to')) {
            $this->error('Option `--to` should be set');
            return true;
        }

        if (!$this->option('from')) {
            $this->error('Option `--form` should be set');
            return true;
        }

        return false;
    }

    protected function resetBcoPhaseOnOrganisations(): void
    {
        $organisationQuery = $this->createQuery('organisation');
        $organisationCount = $organisationQuery->count();

        if ($organisationCount <= 0) {
            if ($this->from) {
                $this->getOutput()->warning("No organisations found with BCO Phase `{$this->from}`");
            } else {
                $this->getOutput()->warning('No organisations found');
            }

            return;
        }

        $this->getOutput()->note("{$organisationCount} organisations found");

        $progress = $this->createProgressBar($organisationCount);

        $organisationQuery->orderBy('uuid')->chunkById(
            $this->chunkCount,
            function (Collection $organisationCollection) use ($progress): void {
                $organisationArray = $organisationCollection->pluck('uuid')->toArray();

                DB::table('organisation')
                    ->whereIn('uuid', $organisationArray)
                    ->update(['bco_phase' => $this->to]);

                $progress->advance($organisationCollection->count());
            },
            'uuid',
        );
    }

    protected function resetBcoPhaseOnCases(): void
    {
        $caseQuery = $this->createQuery('covidcase');
        $caseCount = $caseQuery->count();

        if ($caseCount <= 0) {
            if ($this->from) {
                $this->getOutput()->warning("No cases found with BCO Phase `{$this->from}`");
            } else {
                $this->getOutput()->warning('No cases found');
            }
            return;
        }

        $this->getOutput()->note("{$caseCount} cases found");
        $progress = $this->createProgressBar($caseCount);

        $caseQuery->orderBy('uuid')->chunkById($this->chunkCount, function (Collection $caseCollection) use ($progress): void {
            $caseArray = $caseCollection->pluck('uuid')->toArray();

            DB::table('covidcase')
                ->whereIn('uuid', $caseArray)
                ->update(['bco_phase' => $this->to]);

            $progress->advance($caseCollection->count());
        }, 'uuid');
    }

    protected function createQuery(string $table): Builder
    {
        $query = DB::table($table);

        if ($this->from) {
            $query->where('bco_phase', $this->from);
        }

        return $query;
    }

    protected function createProgressBar(int $count): ProgressBar
    {
        return new ProgressBar($this->getOutput(), $count);
    }
}
