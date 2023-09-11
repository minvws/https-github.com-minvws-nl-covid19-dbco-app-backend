<?php

declare(strict_types=1);

namespace App\Events\OpenAPI;

use Illuminate\Foundation\Events\Dispatchable;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;

class OpenAPIValidationFailedEvent
{
    use Dispatchable;

    public function __construct(
        public readonly ValidationFailed $exception,
        public readonly OperationAddress $operation,
    )
    {
    }
}
