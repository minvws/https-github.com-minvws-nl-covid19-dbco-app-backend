<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\Config;
use App\Services\CaseMetrics\CaseMetricsService;
use App\Services\OrganisationService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

/**
 * Scheduled command refreshing the materialised table containing case metrics for all regional GGD organisations.
 * In development mode, @see TestDataCaseMetrics for seeding and augmenting test data for better metrics simulation.
 */
class CaseMetricsRefreshCommand extends Command
{
    protected $signature = 'case-metrics:refresh
        {--t|timezone= : The timezone used to compute the metrics, defaults to `app.display_timezone` }';

    protected $description = 'Refresh the materialised case metrics for all regional GGD organisations';

    public function __construct(
        private readonly OrganisationService $organisationService,
        private readonly CaseMetricsService $caseMetricsService,
    ) {
        parent::__construct();
    }

    /**
     * @codeCoverageIgnore
     */
    public function handle(): int
    {
        $organisationsUuids = $this->organisationService->listOrganisationUuids();
        $verbose = $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;

        if ($verbose) {
            $bar = $this->output->createProgressBar($organisationsUuids->count());
            $bar->setRedrawFrequency(1);
            $bar->start();
        }

        try {
            $periodEnd = $this->getPeriodEnd();
        } catch (InvalidArgumentException $invalidArgumentException) {
            $this->error($invalidArgumentException->getMessage());

            return self::FAILURE;
        }

        foreach ($organisationsUuids as $organisationUuid) {
            $this->caseMetricsService->refreshForOrganisation($organisationUuid, $periodEnd);

            if ($verbose) {
                $bar->advance();
            }
        }

        return self::SUCCESS;
    }

    public function getPeriodEnd(): CarbonImmutable
    {
        $displayTimezone = $this->option('timezone') ?? Config::string('app.display_timezone');
        Assert::string($displayTimezone);

        return CarbonImmutable::now($displayTimezone)
            ->floorDay()
            ->setTimezone(Config::string('app.timezone'));
    }
}
