<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\ArchiveStaleCompletedCases;
use App\Console\Commands\CaseMetricsRefreshCommand;
use App\Console\Commands\CasesUpdateStatusCommand;
use App\Console\Commands\MetricTest;
use App\Console\Commands\MigrateDataOffHours;
use App\Console\Commands\OsirisRetryCaseExport;
use App\Console\Commands\Policy\PolicyVersionActivatorCommand;
use App\Console\Commands\PurgeSoftDeletedModels;
use App\Console\Commands\RemoveLabelsAndPriorityFromArchivedCases;
use App\Console\Commands\SyncMessageStatus;
use App\Console\Commands\SyncPlaceCounters;
use App\Console\Commands\UnassignExpertQuestionsCommand;
use App\Helpers\FeatureFlagHelper;
use App\Models\Metric\Command\ScheduledCommand;
use App\Services\MetricService;
use Closure;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Stringable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function base_path;
use function fwrite;

use const STDERR;
use const STDOUT;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // every five minutes
        $this->scheduleCommand($schedule, CasesUpdateStatusCommand::class)
            ->everyFiveMinutes()
            ->when(FeatureFlagHelper::isEnabled('case_status_command_enabled'));

        $this->scheduleCommand($schedule, SyncMessageStatus::class)
            ->everyFiveMinutes();

        // every thirty minutes
        $this->scheduleCommand($schedule, CaseMetricsRefreshCommand::class)
            ->everyThirtyMinutes()
            ->when(FeatureFlagHelper::isEnabled('case_metrics_enabled'));

        // daily
        $this->scheduleCommand($schedule, PolicyVersionActivatorCommand::class)
            ->dailyAt('00:00');

        $this->scheduleCommand($schedule, PurgeSoftDeletedModels::class)
            ->dailyAt('01:00')
            ->when(FeatureFlagHelper::isEnabled('purge_soft_deleted_models_enabled'));

        $this->scheduleCommand($schedule, RemoveLabelsAndPriorityFromArchivedCases::class)
            ->dailyAt('02:00');

        $this->scheduleCommand($schedule, ArchiveStaleCompletedCases::class)
            ->dailyAt('03:00');

        $this->scheduleCommand($schedule, UnassignExpertQuestionsCommand::class)
            ->dailyAt('03:00');

        $this->scheduleCommand($schedule, SyncPlaceCounters::class)
            ->dailyAt('04:00');

        $this->scheduleCommand($schedule, OsirisRetryCaseExport::class)
            ->dailyAt('23:00')
            ->when(FeatureFlagHelper::isEnabled('osiris_retry_case_export_enabled'));

        // weekly
        $this->scheduleCommand($schedule, MetricTest::class, ['message' => 'weekly test metric'])
            ->weeklyOn(Schedule::MONDAY, '12:00')
            ->when(FeatureFlagHelper::isEnabled('metric_test_monthly'));

        // migrate off hours
        $this->scheduleCommand($schedule, MigrateDataOffHours::class)
            ->everyMinute()
            ->between('04:00', '06:00')
            ->withoutOverlapping()
            ->when(FeatureFlagHelper::isEnabled('migrate_data_off_hours'));
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    private function scheduleCommand(
        Schedule $schedule,
        string $class,
        array $parameters = [],
        int $secondsToPreventOverlap = 14_400,
    ): Event {
        return $schedule
            ->command($class, $parameters)
            ->withoutOverlapping($secondsToPreventOverlap)
            ->before($this->before($class))
            ->onSuccess($this->onSuccess($class))
            ->onFailure($this->onFailure($class));
    }

    private function before(string $class): Closure
    {
        return function (Stringable $output) use ($class): void {
            $this->updatePrometheusCounter(ScheduledCommand::before($class));

            fwrite(STDOUT, (string) $output);
        };
    }

    private function onSuccess(string $class): Closure
    {
        return function (Stringable $output) use ($class): void {
            $this->updatePrometheusCounter(ScheduledCommand::success($class));

            fwrite(STDOUT, (string) $output);
        };
    }

    private function onFailure(string $class): Closure
    {
        return function (Stringable $output) use ($class): void {
            // @codeCoverageIgnoreStart
            $this->updatePrometheusCounter(ScheduledCommand::failure($class));

            fwrite(STDERR, (string) $output);
            // @codeCoverageIgnoreEnd
        };
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function updatePrometheusCounter(ScheduledCommand $scheduledCommand): void
    {
        /** @var MetricService $metricService */
        $metricService = $this->app->get(MetricService::class);
        $metricService->measure($scheduledCommand);
    }
}
