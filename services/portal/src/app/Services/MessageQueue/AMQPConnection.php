<?php

declare(strict_types=1);

namespace App\Services\MessageQueue;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\Heartbeat\PCNTLHeartbeatSender;

class AMQPConnection implements Connection
{
    private array $options;

    private ?AMQPStreamConnection $connection = null;
    private ?PCNTLHeartbeatSender $heartbeatSender = null;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    private function getConnection(): AMQPStreamConnection
    {
        if ($this->connection === null) {
            $this->connection = new AMQPStreamConnection(
                $this->options['host'],
                $this->options['port'],
                $this->options['username'],
                $this->options['password'],
                $this->options['vhost'],
                false,
                'AMQPLAIN',
                null,
                'en_US',
                3.0,
                3.0,
                null,
                false,
                15,
                0.0,
                null,
            );
        }

        $this->heartbeatSender = new PCNTLHeartbeatSender($this->connection);
        $this->heartbeatSender->register();

        return $this->connection;
    }

    public function getQueue(array $options): Queue
    {
        return new AMQPQueue($this->getConnection(), $options);
    }

    public function stop(): void
    {
        if ($this->heartbeatSender !== null) {
            $this->heartbeatSender->unregister();
        }

        if ($this->connection !== null) {
            $this->connection->close();
        }
    }
}
