<?php

declare(strict_types=1);

namespace Tests\Unit\Scheduler;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Container\BindingResolutionException;

final class SyncMessageStatusSchedulerTest extends SchedulerTestCase
{
    /**
     * @throws BindingResolutionException
     */
    public function testCompanyFeedbackSchedule(): void
    {
        $event = $this->getCommandEvent('message:sync-status');

        $test_date = CarbonImmutable::now()->startOfDay()->setMinute(0);

        for ($i = 0; $i < 60; $i++) {
            $test_date = $test_date->addMinute();
            CarbonImmutable::setTestNow($test_date);

            $filters_pass = $event->filtersPass($this->app);
            $date_passes = $this->isEventDue($event);
            $will_run = $filters_pass && $date_passes;

            // Should only run every 5 minutes
            if ($test_date->minute % 5 === 0) {
                $this->assertTrue($will_run, 'Task should run on ' . $test_date->toDateTimeString());
            } else {
                $this->assertFalse($will_run, 'Task should not run on ' . $test_date->toDateTimeString());
            }
        }
    }
}
