<?php

declare(strict_types=1);

namespace App\Exceptions\Osiris;

use RuntimeException;

use function sprintf;

class CouldNotRetrieveCredentials extends RuntimeException
{
    public static function becauseNoMatchForOrganisation(string $externalId): self
    {
        return new self(sprintf('No match for organisation with external ID %s', $externalId));
    }
}
