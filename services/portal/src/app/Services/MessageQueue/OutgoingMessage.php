<?php

declare(strict_types=1);

namespace App\Services\MessageQueue;

use JsonSerializable;

class OutgoingMessage implements Message, JsonSerializable
{
    private string $id;
    private array $data;

    public function __construct(string $id, array $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function jsonSerialize(): array
    {
        return ['id' => $this->id, 'data' => $this->data];
    }
}
