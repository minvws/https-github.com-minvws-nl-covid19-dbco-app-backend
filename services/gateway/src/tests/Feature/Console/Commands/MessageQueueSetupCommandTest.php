<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Tests\TestCase;
use VladimirYuldashev\LaravelQueueRabbitMQ\Console\QueueDeclareCommand;

use function config;
use function sprintf;

final class MessageQueueSetupCommandTest extends TestCase
{
    public function testDeclareQueues(): void
    {
        $queue = $this->faker->word();
        config()->set('queue.connections.rabbitmq.queue', $queue);

        $queueDeclareCommand = $this->createMock(QueueDeclareCommand::class);
        $queueDeclareCommand->expects($this->atLeastOnce())->method('run')->willReturn(Command::SUCCESS);
        $this->app->instance(QueueDeclareCommand::class, $queueDeclareCommand);

        $this->artisan('message-queue:setup')
            ->assertOk()
            ->expectsOutputToContain(sprintf('Successfully declared queue: "%s"', $queue));
    }

    public function testFailWhenDeclareQueueCommandFailsWithExitCode(): void
    {
        $queue = $this->faker->word();
        config()->set('queue.connections.rabbitmq.queue', $queue);

        $queueDeclareCommand = $this->createMock(QueueDeclareCommand::class);
        $queueDeclareCommand->expects($this->once())->method('run')->willReturn(Command::FAILURE);
        $this->app->instance(QueueDeclareCommand::class, $queueDeclareCommand);

        $this->artisan('message-queue:setup')
            ->assertFailed()
            ->expectsOutputToContain(sprintf('Failed to declare queue: "%s"', $queue));
    }
}
