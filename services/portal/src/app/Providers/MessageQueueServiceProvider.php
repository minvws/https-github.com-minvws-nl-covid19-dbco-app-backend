<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\MessageQueue\MessageQueueService;
use Illuminate\Support\ServiceProvider;

class MessageQueueServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->when(MessageQueueService::class)
            ->needs('$config')
            ->giveConfig('messagequeue');
    }
}
