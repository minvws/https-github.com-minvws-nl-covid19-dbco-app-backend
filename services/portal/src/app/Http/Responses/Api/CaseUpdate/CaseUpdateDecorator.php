<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\CaseUpdate;

use App\Models\Eloquent\CaseUpdate;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function assert;

class CaseUpdateDecorator implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof CaseUpdate);

        $container->uuid = $value->uuid;
        $container->receivedAt = $value->received_at;
        $container->source = $value->source;
    }
}
