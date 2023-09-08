<?php

declare(strict_types=1);

namespace App\Providers;

use App\Helpers\Config;

class ClockworkServiceProvider extends \Clockwork\Support\Laravel\ClockworkServiceProvider
{
    public function boot(): void
    {
        if (!$this->isClockworkEnabled()) {
            return;
        }

        parent::boot();
    }

    public function register(): void
    {
        if (!$this->isClockworkEnabled()) {
            return;
        }

        parent::register();
    }

    private function isClockworkEnabled(): bool
    {
        return Config::boolean('clockwork.enable');
    }
}
