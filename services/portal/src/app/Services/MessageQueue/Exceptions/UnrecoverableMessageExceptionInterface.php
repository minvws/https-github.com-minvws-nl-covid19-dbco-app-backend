<?php

declare(strict_types=1);

namespace App\Services\MessageQueue\Exceptions;

/**
 * Marker interface for exceptions to indicate that handling of a queue message will fail permanently.
 *
 * It can be used to let a queue message fail without the use of the retry mechanism.
 **/
interface UnrecoverableMessageExceptionInterface
{
}
