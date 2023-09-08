<?php

declare(strict_types=1);

namespace Tests\Unit\Scheduler;

use Carbon\CarbonImmutable;
use Cron\CronExpression;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\TestCase;

use function app;
use function collect;
use function stripos;

abstract class SchedulerTestCase extends TestCase
{
    /**
     * Get the event matching the given command signature from the scheduler
     *
     * @throws BindingResolutionException
     */
    protected function getCommandEvent(string $command_signature): Event
    {
        /** @var Schedule $schedule */
        $schedule = app()->make(Schedule::class);

        $event = collect($schedule->events())->first(static function (Event $event) use ($command_signature) {
            return stripos($event->command, $command_signature);
        });

        if (!$event) {
            $this->fail('Event for ' . $command_signature . ' not found');
        }

        return $event;
    }

    /**
     * Determine if the Cron expression passes.
     *
     * Copied from the protected method Illuminate\Console\Scheduling\Event@isEventDue
     */
    protected function isEventDue(Event $event): ?bool
    {
        $date = CarbonImmutable::now();

        if ($event->timezone) {
            $date->setTimezone($event->timezone);
        }

        return CronExpression::factory($event->expression)->isDue($date->toDateTimeString());
    }
}
