<?php

declare(strict_types=1);

namespace App\Services\MessageQueue;

interface IncomingMessage extends Message
{
    public function ack(): void;

    public function nack(bool $requeue): void;
}
