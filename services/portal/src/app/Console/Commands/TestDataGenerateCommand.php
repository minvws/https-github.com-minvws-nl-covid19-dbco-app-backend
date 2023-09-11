<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\TestDataSeeder;
use Illuminate\Console\Command;
use Throwable;

use function sprintf;

/**
 * Create a set of realistic test data.
 *
 * @codeCoverageIgnore
 */
class TestDataGenerateCommand extends Command
{
    /** @var string */
    protected $signature = 'test-data:generate
        {amountOfCases=100}
        {--o|organisationUuids=* : defaults to demo-organisation (if none supplied)}
    ';

    /** @var string */
    protected $description = 'Generate a set of realistic test data';

    public function handle(TestDataSeeder $testDataSeeder): int
    {
        $amountOfCases = (int) $this->argument('amountOfCases');
        $organisationUuids = (array) $this->option('organisationUuids');

        $bar = $this->output->createProgressBar($amountOfCases);
        $bar->setRedrawFrequency(1);
        $bar->start();

        try {
            $testDataSeeder->run($amountOfCases, $organisationUuids, fn() => $bar->advance());
        } catch (Throwable $exception) {
            $this->output->error(sprintf('failed: %s', $exception->getMessage()));
            return self::FAILURE;
        }

        $bar->finish();

        $this->output->writeln('');

        return self::SUCCESS;
    }
}
