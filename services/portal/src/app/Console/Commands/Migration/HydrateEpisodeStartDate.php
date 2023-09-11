<?php

declare(strict_types=1);

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Webmozart\Assert\Assert;

use function ceil;
use function collect;
use function sprintf;

class HydrateEpisodeStartDate extends Command
{
    private const COUNT_ELIGIBLE_CASES_QUERY = <<<'SQL'
        SELECT COUNT(uuid) AS caseCount
        FROM covidcase
        WHERE episode_start_date IS NULL
    SQL;

    private const SELECT_CASE_UUID_QUERY = <<<'SQL'
        SELECT uuid
        FROM covidcase
        WHERE episode_start_date IS NULL
        LIMIT ?
    SQL;

    protected $signature = 'migration:hydrate-episode-start-date
        {--b|batchSize=1000}
    ';

    protected $description = 'Initial hydration of case attribute `episode_start_date`';

    private int $batchSize;

    /**
     * @codeCoverageIgnore
     */
    public function handle(): int
    {
        $this->batchSize = (int) $this->option('batchSize');
        $updateCount = $this->getEligibleCaseCount();
        $batchCount = (int) ceil($updateCount / $this->batchSize);

        $bar = $this->output->createProgressBar($batchCount);
        $bar->setRedrawFrequency(1);
        $bar->start();

        for ($i = 0; $i < $batchCount; $i++) {
            $caseUuids = $this->getCaseUuidChunk();
            $commaSeparatedUuids = '"' . $caseUuids->implode('uuid', '","') . '"';
            DB::update(
                <<<SQL
                UPDATE covidcase
                SET episode_start_date = created_at
                WHERE uuid IN ($commaSeparatedUuids);
                SQL,
            );
            $bar->advance();
        }

        $bar->finish();
        $this->output->writeln('');
        $this->output->writeln(sprintf('Number of cases left to hydrate: %d', $this->getEligibleCaseCount()));

        return SymfonyCommand::SUCCESS;
    }

    /**
     * @codeCoverageIgnore
     */
    private function getEligibleCaseCount(): int
    {
        /** @var object $row */
        $row = DB::selectOne(self::COUNT_ELIGIBLE_CASES_QUERY);
        Assert::propertyExists($row, 'caseCount');
        return (int) $row->caseCount;
    }

    /**
     * @codeCoverageIgnore
     */
    private function getCaseUuidChunk(): Collection
    {
        return collect(DB::select(self::SELECT_CASE_UUID_QUERY, [$this->batchSize]));
    }
}
