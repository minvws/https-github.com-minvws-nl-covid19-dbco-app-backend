<?php

declare(strict_types=1);

namespace App\Services\MessageQueue;

interface Connection
{
    public function __construct(array $options);

    public function getQueue(array $options): Queue;

    public function stop(): void;
}
