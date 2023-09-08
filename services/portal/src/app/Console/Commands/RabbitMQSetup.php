<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\Config;
use Illuminate\Console\Command;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use Throwable;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Connectors\RabbitMQConnector;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

use function sprintf;

final class RabbitMQSetup extends Command
{
    /** @var string */
    protected $signature = 'rabbitmq:setup';

    /** @var string */
    protected $description = 'Configures queues in RabbitMQ to use within the application';

    public function handle(RabbitMQConnector $connector): int
    {
        $queues = [
            Config::string('queue.connections.rabbitmq.queue'),
            Config::string('services.osiris.queue.name'),
        ];

        try {
            $rabbitMq = $connector->connect(Config::array('queue.connections.rabbitmq'));
            $this->declareQueues($rabbitMq, $queues);

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            $this->line($throwable->getMessage());
            $this->newLine();
            $this->alert('An error occurred while declaring queues. Remaining queues are not created until this issue is resolved.');

            return self::FAILURE;
        }
    }

    /**
     * @param array<string> $queues
     *
     * @throws AMQPProtocolChannelException
     */
    private function declareQueues(RabbitMQQueue $rabbitMq, array $queues): void
    {
        $this->info('Started to declare queues in RabbitMQ...');

        foreach ($queues as $queue) {
            if ($rabbitMq->isQueueExists($queue)) {
                $this->line(sprintf('Queue "%s" already exists.', $queue));
                continue;
            }

            $rabbitMq->declareQueue($queue);
            $this->line(sprintf('Successfully created queue: "%s"', $queue));
        }

        $this->info('Finished declaring queues in RabbitMQ');
    }
}
