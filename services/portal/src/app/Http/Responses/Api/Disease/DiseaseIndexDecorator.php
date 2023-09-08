<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Disease;

use App\Models\Disease\Disease;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function assert;

class DiseaseIndexDecorator implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof Disease);
        $container->id = $value->id;
        $container->code = $value->code;
        $container->name = $value->name;
        $container->currentVersion = $value->currentVersion;
        $container->isActive = isset($value->currentVersion);
    }
}
