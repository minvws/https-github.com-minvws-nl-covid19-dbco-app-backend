<?php

declare(strict_types=1);

namespace App\Services\MessageQueue;

interface Message
{
    public function getId(): string;

    public function getData(): array;
}
