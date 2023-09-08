<?php

declare(strict_types=1);

namespace App\Services\Disease;

use Exception;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

class InvalidOperationException extends Exception implements Encodable
{
    public function encode(EncodingContainer $container): void
    {
        $container->code = 'invalid_operation';
        $container->message = $this->getMessage();
    }
}
