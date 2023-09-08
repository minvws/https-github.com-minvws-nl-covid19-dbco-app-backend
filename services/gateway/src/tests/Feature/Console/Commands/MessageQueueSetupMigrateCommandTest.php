<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\MessageQueueSetupCommand;
use Symfony\Component\Console\Command\Command;
use Tests\TestCase;
use VladimirYuldashev\LaravelQueueRabbitMQ\Console\ExchangeDeleteCommand;
use VladimirYuldashev\LaravelQueueRabbitMQ\Console\QueueDeleteCommand;

final class MessageQueueSetupMigrateCommandTest extends TestCase
{
    public function testCommandStopsWhenNotConfirmedByUser(): void
    {
        $this->artisan('message-queue:setup-migrate')
            ->expectsConfirmation('[CAUTION] this command will drop queues! Any messages in the queues will get lost. Do you want to proceed?')
            ->expectsOutputToContain('Aborted command as you wish')
            ->assertOk();
    }

    public function testCommandMigratesRabbitMqSetup(): void
    {
        $queueDeleteCommand = $this->createMock(QueueDeleteCommand::class);
        $queueDeleteCommand->expects($this->exactly(2))->method('run')
            ->willReturn(Command::SUCCESS);

        $exchangeDeleteCommand = $this->createMock(ExchangeDeleteCommand::class);
        $exchangeDeleteCommand->expects($this->once())->method('run')
            ->willReturn(Command::SUCCESS);

        $messageQueueSetupCommand = $this->createMock(MessageQueueSetupCommand::class);
        $messageQueueSetupCommand->expects($this->once())->method('run')
            ->willReturn(Command::SUCCESS);

        $this->app->instance(QueueDeleteCommand::class, $queueDeleteCommand);
        $this->app->instance(ExchangeDeleteCommand::class, $exchangeDeleteCommand);
        $this->app->instance(MessageQueueSetupCommand::class, $messageQueueSetupCommand);

        $this->artisan('message-queue:setup-migrate')
            ->expectsConfirmation('[CAUTION] this command will drop queues! Any messages in the queues will get lost. Do you want to proceed?', 'yes')
            ->expectsOutputToContain('Successfully dropped "test_result" queue')
            ->expectsOutputToContain('Successfully dropped "test_result.failed" queue')
            ->expectsOutputToContain('Successfully dropped "dbco" exchange')
            ->expectsOutputToContain('Successfully configured queues')
            ->expectsOutputToContain('Successful migration of message queues')
            ->assertOk();
    }

    public function testCommandHandlesErrorDuringDroppingQueues(): void
    {
        $queueDeleteCommand = $this->createMock(QueueDeleteCommand::class);
        $queueDeleteCommand->expects($this->once())->method('run')
            ->willReturn(Command::FAILURE);

        $this->app->instance(QueueDeleteCommand::class, $queueDeleteCommand);

        $this->artisan('message-queue:setup-migrate')
            ->expectsConfirmation('[CAUTION] this command will drop queues! Any messages in the queues will get lost. Do you want to proceed?', 'yes')
            ->assertFailed();
    }

    public function testCommandHandlesErrorDuringDroppingExchange(): void
    {
        $queueDeleteCommand = $this->createMock(QueueDeleteCommand::class);
        $queueDeleteCommand->expects($this->exactly(2))->method('run')
            ->willReturn(Command::SUCCESS);

        $exchangeDeleteCommand = $this->createMock(ExchangeDeleteCommand::class);
        $exchangeDeleteCommand->expects($this->once())->method('run')
            ->willReturn(Command::FAILURE);

        $this->app->instance(QueueDeleteCommand::class, $queueDeleteCommand);
        $this->app->instance(ExchangeDeleteCommand::class, $exchangeDeleteCommand);

        $this->artisan('message-queue:setup-migrate')
            ->expectsConfirmation('[CAUTION] this command will drop queues! Any messages in the queues will get lost. Do you want to proceed?', 'yes')
            ->expectsOutputToContain('Failed to drop "dbco" exchange')
            ->assertFailed();
    }

    public function testCommandHandlesErrorDuringSetupOfRabbitMq(): void
    {
        $queueDeleteCommand = $this->createMock(QueueDeleteCommand::class);
        $queueDeleteCommand->expects($this->exactly(2))->method('run')
            ->willReturn(Command::SUCCESS);

        $exchangeDeleteCommand = $this->createMock(ExchangeDeleteCommand::class);
        $exchangeDeleteCommand->expects($this->once())->method('run')
            ->willReturn(Command::SUCCESS);

        $messageQueueSetupCommand = $this->createMock(MessageQueueSetupCommand::class);
        $messageQueueSetupCommand->expects($this->once())->method('run')
            ->willReturn(Command::FAILURE);

        $this->app->instance(QueueDeleteCommand::class, $queueDeleteCommand);
        $this->app->instance(ExchangeDeleteCommand::class, $exchangeDeleteCommand);
        $this->app->instance(MessageQueueSetupCommand::class, $messageQueueSetupCommand);

        $this->artisan('message-queue:setup-migrate')
            ->expectsConfirmation('[CAUTION] this command will drop queues! Any messages in the queues will get lost. Do you want to proceed?', 'yes')
            ->expectsOutputToContain('Failed to configure queues')
            ->assertFailed();
    }
}
