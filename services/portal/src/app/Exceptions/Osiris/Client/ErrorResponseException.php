<?php

declare(strict_types=1);

namespace App\Exceptions\Osiris\Client;

use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class ErrorResponseException extends BaseClientException
{
    public function __construct(
        public readonly string $reason,
        /** @var array<int,string> $errors */
        public readonly array $errors = [],
    ) {
        parent::__construct(
            sprintf(
                'Osiris request failed with errors in response; %s',
                json_encode(['reason' => $this->reason, 'errors' => $this->errors], flags: JSON_THROW_ON_ERROR),
            ),
        );
    }
}
