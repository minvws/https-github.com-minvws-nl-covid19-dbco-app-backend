<?php

declare(strict_types=1);

namespace App\Services\MessageQueue\Exceptions;

class UnrecoverableMessageException extends MessageQueueException implements UnrecoverableMessageExceptionInterface
{
}
