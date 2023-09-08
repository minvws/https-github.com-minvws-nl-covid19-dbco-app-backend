<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RuntimeException;
use Throwable;
use VladimirYuldashev\LaravelQueueRabbitMQ\Console\QueueDeclareCommand;
use Webmozart\Assert\Assert;

use function config;
use function sprintf;

class MessageQueueSetupCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'message-queue:setup';

    /**
     * @var string
     */
    protected $description = 'Configures queue in RabbitMQ';

    public function handle(): int
    {
        try {
            $this->declareQueue($this->getQueue());
            return self::SUCCESS;
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());
            return self::FAILURE;
        }
    }

    private function declareQueue(string $queue): void
    {
        $command = QueueDeclareCommand::class;
        $arguments = ['name' => $queue];

        if (self::SUCCESS !== $this->callSilent($command, $arguments)) {
            throw new RuntimeException(sprintf('Failed to declare queue: "%s"', $queue));
        }

        $this->info(sprintf('Successfully declared queue: "%s"', $queue));
    }

    private function getQueue(): string
    {
        $queue = config('queue.connections.rabbitmq.queue');
        Assert::string($queue);

        return $queue;
    }
}
