<?php

declare(strict_types=1);

namespace App\Exceptions\Osiris\CaseExport;

use Throwable;

use function sprintf;

final class CaseExportException extends BaseCaseExportException
{
    private function __construct(string $reason, Throwable $previous)
    {
        parent::__construct(
            sprintf('Could not export case to Osiris; %s', $reason),
            $previous->getCode(),
            $previous,
        );
    }

    public static function fromThrowable(Throwable $previous): self
    {
        return new self($previous->getMessage(), $previous);
    }
}
