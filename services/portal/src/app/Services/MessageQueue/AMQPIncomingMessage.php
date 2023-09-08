<?php

declare(strict_types=1);

namespace App\Services\MessageQueue;

use PhpAmqpLib\Message\AMQPMessage;

use function json_decode;

class AMQPIncomingMessage implements IncomingMessage
{
    private AMQPMessage $message;

    private string $id;
    private array $data;

    public function __construct(AMQPMessage $message)
    {
        $this->message = $message;

        $data = json_decode($this->message->getBody(), true);
        $this->id = $data['id'] ?? '';
        $this->data = $data['data'] ?? [];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function ack(): void
    {
        $this->message->ack();
    }

    public function nack(bool $requeue): void
    {
        $this->message->nack($requeue);
    }
}
