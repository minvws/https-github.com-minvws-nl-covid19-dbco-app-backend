<?php

declare(strict_types=1);

namespace App\Services\MessageQueue;

use Closure;
use ErrorException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

use function json_encode;

class AMQPQueue implements Queue
{
    private AMQPStreamConnection $connection;
    private array $options;

    private ?AMQPChannel $channel = null;

    public function __construct(AMQPStreamConnection $connection, array $options)
    {
        $this->connection = $connection;
        $this->options = $options;
    }

    private function getChannel(): AMQPChannel
    {
        $channel = $this->connection->channel();
        if ($this->options['declare_exchange_and_queue']) {
            $channel->exchange_declare($this->options['exchange'], 'topic', false, true, false, false);
            $channel->queue_declare(
                $this->options['queue'],
                false,
                true,
                false,
                false,
                false,
                new AMQPTable([
                    'x-queue-type' => 'quorum',
                    'x-dead-letter-exchange' => $this->options['dead_letter_exchange'],
                    'x-dead-letter-routing-key' => $this->options['dead_letter_routing_key'],
                    'x-delivery-limit' => $this->options['delivery_limit'],
                ]),
            );
            $channel->queue_bind($this->options['queue'], $this->options['exchange'], $this->options['routing_key']);

            $channel->queue_declare('test_result.failed', false, true, false, false, false);

            $channel->queue_bind('test_result.failed', 'dbco', 'dbco.test_result.failed');
        }
        return $channel;
    }

    public function get(): ?IncomingMessage
    {
        $channel = $this->getChannel();
        $message = $this->getChannel()->basic_get($this->options['queue'], true);
        $channel->close();

        if ($message === null) {
            return null;
        }

        return new AMQPIncomingMessage($message);
    }

    /**
     * @throws ErrorException
     */
    public function loop(Closure $callback): void
    {
        $this->channel = $this->getChannel();

        $this->channel->basic_consume(
            $this->options['queue'],
            $this->options['consumer_tag'] ?? self::class,
            false,
            false,
            false,
            false,
            static fn (AMQPMessage $message) => $callback(new AMQPIncomingMessage($message))
        );

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function stop(): void
    {
        if ($this->channel !== null) {
            $this->channel->close();
            $this->channel = null;
        }
    }

    public function publish(Message $message): void
    {
        /** @var string $body */
        $body = json_encode($message);
        $channel = $this->getChannel();
        $channel->basic_publish(new AMQPMessage($body), $this->options['exchange'], $this->options['routing_key']);
        $channel->close();
    }
}
