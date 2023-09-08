<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Helpers\Config;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Tests\Feature\FeatureTestCase;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Connectors\RabbitMQConnector;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

use function sprintf;

final class RabbitMQSetupTest extends FeatureTestCase
{
    public function testDeclareQueues(): void
    {
        $this->mock(RabbitMQConnector::class, static function (MockInterface $rabbitMqConnector): void {
            /** @var RabbitMQQueue&MockInterface $rabbitMqQueue */
            $rabbitMqQueue = Mockery::mock(RabbitMQQueue::class);

            $rabbitMqQueue
                ->shouldReceive('isQueueExists')
                ->atLeast()
                ->once()
                ->andReturnFalse();

            $rabbitMqQueue
                ->shouldReceive('declareQueue')
                ->ordered()
                ->atLeast()
                ->once()
                ->with(Config::string('queue.connections.rabbitmq.queue'));

            $rabbitMqQueue
                ->shouldReceive('declareQueue')
                ->ordered()
                ->atLeast()
                ->once()
                ->with(Config::string('services.osiris.queue.name'));

            $rabbitMqConnector->expects('connect')->andReturn($rabbitMqQueue);
        });

        $this->artisan('rabbitmq:setup')
            ->assertOk()
            ->expectsOutputToContain('Started to declare queues in RabbitMQ...')
            ->expectsOutputToContain(
                sprintf(
                    'Successfully created queue: "%s"',
                    Config::string('queue.connections.rabbitmq.queue'),
                ),
            )
            ->expectsOutputToContain(
                sprintf(
                    'Successfully created queue: "%s"',
                    Config::string('services.osiris.queue.name'),
                ),
            )
            ->expectsOutputToContain('Finished declaring queues in RabbitMQ');
    }

    public function testSkipIfQueueAlreadyExists(): void
    {
        $this->mock(RabbitMQConnector::class, static function (MockInterface $rabbitMqConnector): void {
            /** @var RabbitMQQueue&MockInterface $rabbitMqQueue */
            $rabbitMqQueue = Mockery::mock(RabbitMQQueue::class);

            $rabbitMqQueue
                ->shouldReceive('isQueueExists')
                ->atLeast()
                ->once()
                ->andReturnTrue();

            $rabbitMqConnector->expects('connect')->andReturn($rabbitMqQueue);
        });

        $this->artisan('rabbitmq:setup')
            ->assertOk()
            ->expectsOutputToContain('Started to declare queues in RabbitMQ...')
            ->expectsOutputToContain(
                sprintf(
                    'Queue "%s" already exists.',
                    Config::string('queue.connections.rabbitmq.queue'),
                ),
            );
    }

    public function testFailWhenDeclareQueueCommandFailsWithException(): void
    {
        $exceptionMessage = $this->faker->sentence;
        $exception = new RuntimeException($exceptionMessage);

        $this->mock(RabbitMQConnector::class, static function (MockInterface $rabbitMqConnector) use ($exception): void {
            /** @var RabbitMQQueue&MockInterface $rabbitMqQueue */
            $rabbitMqQueue = Mockery::mock(RabbitMQQueue::class);

            $rabbitMqQueue
                ->shouldReceive('isQueueExists')
                ->atLeast()
                ->once()
                ->andReturnFalse();

            $rabbitMqQueue
                ->shouldReceive('declareQueue')
                ->atLeast()
                ->once()
                ->andThrow($exception);

            $rabbitMqConnector->expects('connect')->andReturn($rabbitMqQueue);
        });

        $this->artisan('rabbitmq:setup')
            ->assertFailed()
            ->expectsOutputToContain('Started to declare queues in RabbitMQ...')
            ->expectsOutputToContain($exceptionMessage)
            ->expectsOutputToContain(
                'An error occurred while declaring queues. Remaining queues are not created until this issue is resolved.',
            );
    }
}
