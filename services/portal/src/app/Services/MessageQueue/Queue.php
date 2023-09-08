<?php

declare(strict_types=1);

namespace App\Services\MessageQueue;

use Closure;

interface Queue
{
    public function publish(Message $message): void;

    public function get(): ?IncomingMessage;

    public function loop(Closure $callback): void;

    public function stop(): void;
}
