<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Console\Commands\CasesUpdateStatusCommand;
use App\Models\Metric\Command\ScheduledCommand;
use App\Models\Metric\CounterMetric;
use App\Repositories\Metric\MetricRepository;
use Carbon\CarbonImmutable;
use Mockery;
use Mockery\MockInterface;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

class KernelTest extends FeatureTestCase
{
    public function testScheduleUpdatesPrometheusCounters(): void
    {
        ConfigHelper::enableFeatureFlag('case_status_command_enabled');

        $this->mock(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureCounter')
                ->once()
                ->with(Mockery::on(static function (ScheduledCommand $scheduledCommand): bool {
                    if ($scheduledCommand->getName() !== 'scheduled_command_counter') {
                        return false;
                    }

                    $expectedLabels = [
                        'class' => CasesUpdateStatusCommand::class,
                        'status' => 'before',
                    ];
                    return $scheduledCommand->getLabels() === $expectedLabels;
                }));

            $mock->expects('measureCounter')
                ->once()
                ->with(Mockery::on(static function (ScheduledCommand $scheduledCommand): bool {
                    if ($scheduledCommand->getName() !== 'scheduled_command_counter') {
                        return false;
                    }

                    $expectedLabels = [
                        'class' => CasesUpdateStatusCommand::class,
                        'status' => 'success',
                    ];
                    return $scheduledCommand->getLabels() === $expectedLabels;
                }));
        });

        $artisan = $this->artisan('schedule:test --name cases:update-status');
        $artisan->execute();
    }

    public function testScheduleRunAlwaysTriggersMetric(): void
    {
        // set time to make sure the CalculateAge::command is scheduled
        CarbonImmutable::setTestNow(CarbonImmutable::now()->setTime(2, 0));

        $mock = $this->mock(MetricRepository::class);
        $mock->expects('measureCounter')
            ->atLeast()
            ->once()
            ->withArgs(static function (CounterMetric $metric) {
                return $metric->getName() === 'scheduled_command_counter'
                    && $metric->getLabels()['status'] === 'before';
            });

        $mock->expects('measureCounter')
            ->atLeast()
            ->once()
            ->withArgs(static function (CounterMetric $metric) {
                return $metric->getName() === 'scheduled_command_counter'
                    && $metric->getLabels()['status'] === 'success';
            });

        $this->artisan('schedule:run')->execute();
    }
}
