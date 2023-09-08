<?php

declare(strict_types=1);

namespace App\Services\MessageQueue;

interface IncomingMessageProcessor
{
    public function processIncomingMessage(IncomingMessage $incomingMessage): void;
}
