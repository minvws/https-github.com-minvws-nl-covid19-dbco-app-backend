<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Services\MessageQueue\Exceptions\UnrecoverableMessageException;
use Throwable;

use function sprintf;

final class OrganisationNotFoundException extends UnrecoverableMessageException
{
    private function __construct(string $message, Throwable $previous)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function withHpZoneCode(string $hpZoneCode, Throwable $previous): self
    {
        return new self(
            sprintf('Could not find organisation with hpZoneCode: "%s"', $hpZoneCode),
            $previous,
        );
    }
}
