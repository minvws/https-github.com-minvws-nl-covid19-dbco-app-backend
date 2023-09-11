<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\RateLimited;

use App\Jobs\RateLimited\RabbitMQJob;
use Faker\Factory;
use Generator;
use Illuminate\Foundation\Application;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Unit\UnitTestCase;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

use function json_encode;

class RabbitMQJobTest extends UnitTestCase
{
    private RabbitMQJob $job;
    private RabbitMQQueue&MockObject $mockQueue;
    private AMQPMessage&MockObject $mockMessage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockQueue = $this->createMock(RabbitMQQueue::class);
        $this->mockQueue->expects($this->once())
            ->method('ack');

        $this->mockMessage = $this->createMock(AMQPMessage::class);
        $this->mockMessage->method('getBody')
            ->willReturn(json_encode([$this->faker->word()]));

        $app = $this->createMock(Application::class);

        $this->job = new RabbitMQJob($app, $this->mockQueue, $this->mockMessage, $this->faker->word(), $this->faker->word());
    }

    /**
     * @throws AMQPProtocolChannelException
     */
    public function testPostponeSetsReleasedAndPosponedToTrue(): void
    {
        $this->mockMessage->expects($this->once())
            ->method('get_properties')
            ->willReturn([]);

        $this->mockQueue->expects($this->once())
            ->method('laterRaw');

        $this->assertFalse($this->job->isReleased());

        $this->job->postpone($this->faker->numberBetween(1, 59));

        $this->assertTrue($this->job->isReleased());
        $this->assertTrue($this->job->isPostponed());
    }

    /**
     * @throws AMQPProtocolChannelException
     */
    #[DataProvider('getAttemptsProvider')]
    public function testPostponeParsesLaravelAttemptsHeader(mixed $attempts, int $expectation): void
    {
        $this->mockMessage->expects($this->once())
            ->method('get_properties')
            ->willReturn($attempts === null
                ? []
                : [
                    'application_headers' => new AMQPTable([
                        'laravel' => [
                            'attempts' => $attempts,
                        ],
                    ]),
                ]);

        $this->mockQueue->expects($this->once())
            ->method('laterRaw')
            ->willReturnCallback(function ($_duration, $_body, $_queue, $attempts) use ($expectation): void {
                $this->assertIsInt($attempts);
                $this->assertEquals($expectation, $attempts);
            });

        $this->job->postpone($this->faker->numberBetween(1, 59));
    }

    public static function getAttemptsProvider(): Generator
    {
        $faker = Factory::create('nl_NL');
        $attempts = $faker->numberBetween(0, 150);
        // mixed $attempts, int $expectation
        yield 'attempts header with integer value' => [$attempts, $attempts];
        yield 'attempts header with numeric value' => [(string) $attempts, $attempts];
        yield 'attempts header with non-numeric value' => [$faker->word(), 0];
        yield 'attempts header with no value' => [null, 0];
    }
}
