<?php

namespace App\Console;

use App\Console\Commands\JwtParseCommand;
use App\Console\Commands\MessageQueueSetupCommand;
use App\Console\Commands\MessageQueueSetupMigrateCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        MessageQueueSetupCommand::class,
        MessageQueueSetupMigrateCommand::class,
        JwtParseCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
