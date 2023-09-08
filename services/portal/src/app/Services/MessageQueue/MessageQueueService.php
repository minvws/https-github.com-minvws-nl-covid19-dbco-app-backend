<?php

declare(strict_types=1);

namespace App\Services\MessageQueue;

use App\Services\MessageQueue\Exceptions\MessageQueueException;
use Illuminate\Contracts\Container\Container;
use Throwable;

use function array_filter;
use function count;
use function in_array;

class MessageQueueService
{
    private Container $container;
    private array $config;

    /** @var array<string, Connection> */
    private array $connections = [];

    /** @var array<string, Queue> */
    private array $queues = [];

    public function __construct(Container $container, array $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    /**
     * @throws MessageQueueException
     */
    public function getConnection(string $connectionName): Connection
    {
        if (isset($this->connections[$connectionName])) {
            return $this->connections[$connectionName];
        }

        $connectionOptions = $this->config['connections'][$connectionName] ?? null;
        if (!isset($connectionOptions)) {
            throw new MessageQueueException("Invalid connection name \"$connectionName\"!");
        }

        $connectionType = $connectionOptions['type'] ?? null;
        if (!isset($connectionType)) {
            throw new MessageQueueException("Unknown connection type for connection  \"$connectionName\"!");
        }

        $connectionClass = $this->config['types'][$connectionType] ?? null;
        if (!isset($connectionClass)) {
            throw new MessageQueueException("Invalid connection type \"$connectionType\ for connection  \"$connectionName\"!");
        }

        try {
            $connection = $this->container->make($connectionClass, ['options' => $connectionOptions]);
        } catch (Throwable $e) {
            throw new MessageQueueException('Could not instantiate connection for class "' . $connectionClass . '"', 0, $e);
        }

        $this->connections[$connectionName] = $connection;
        return $connection;
    }

    /**
     * @throws MessageQueueException
     */
    public function getQueue(string $queueName): Queue
    {
        if (isset($this->queues[$queueName])) {
            return $this->queues[$queueName];
        }

        $queueConfig = $this->config['queues'][$queueName] ?? null;
        if (!isset($queueConfig)) {
            throw new MessageQueueException("Invalid queue name \"$queueName\"!");
        }

        $connectionName = $queueConfig['connection'] ?? 'default';
        $connection = $this->getConnection($connectionName);

        $queue = $connection->getQueue($queueConfig);
        $this->queues[$queueName] = $queue;
        return $queue;
    }

    /**
     * @throws MessageQueueException
     */
    private function getProcessorsForQueue(string $queueName): array
    {
        $processors = array_filter(
            $this->config['processors'] ?? [],
            static fn($p) => in_array($queueName, $p['queues'] ?? [], true)
        ) ?? [];

        if (count($processors) === 0) {
            throw new MessageQueueException("No processor found for queue \"$queueName\"");
        }

        return $processors;
    }

    /**
     * @throws MessageQueueException
     */
    public function processIncomingMessage(string $queueName, IncomingMessage $message): void
    {
        $processors = $this->getProcessorsForQueue($queueName);

        foreach ($processors as $processor) {
            try {
                $class = $processor['class'] ?? IncomingMessageProcessor::class;
                $processor = $this->container->get($processor['class']);
            } catch (Throwable $e) {
                throw new MessageQueueException('Could not instantiate incoming message processor for class "' . $class . '"', 0, $e);
            }

            if (!$processor instanceof IncomingMessageProcessor) {
                throw new MessageQueueException('Invalid incoming message processor for class "' . $class . '"');
            }

            $processor->processIncomingMessage($message);
        }
    }

    public function stop(): void
    {
        foreach ($this->connections as $connection) {
            $connection->stop();
        }
    }
}
