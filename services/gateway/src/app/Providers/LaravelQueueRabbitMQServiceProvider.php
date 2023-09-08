<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Connectors\RabbitMQConnector;

final class LaravelQueueRabbitMQServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var QueueManager $queue */
        $queue = $this->app['queue'];

        $queue->addConnector('rabbitmq', function (): RabbitMQConnector {
            /** @var Dispatcher $dispatcher */
            $dispatcher = $this->app['events'];

            return new RabbitMQConnector($dispatcher);
        });
    }
}
