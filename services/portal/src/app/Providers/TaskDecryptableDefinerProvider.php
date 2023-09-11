<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Task\DefaultTaskDecryptableDefiner;
use App\Services\Task\TaskDecryptableDefiner;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Webmozart\Assert\Assert;

final class TaskDecryptableDefinerProvider extends ServiceProvider
{
    private Config $config;

    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->config = $this->app->make(Config::class);
    }

    public function register(): void
    {
        $this->app->singleton(TaskDecryptableDefiner::class, function () {
            $days = $this->config->get('misc.encryption.task_availability_in_days');

            Assert::numeric($days);

            return new DefaultTaskDecryptableDefiner((int) $days);
        });
    }
}
