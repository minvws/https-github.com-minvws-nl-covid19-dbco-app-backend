<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Disease;

use App\Models\Disease\DiseaseModel;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function assert;

class DiseaseModelIndexDecorator implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof DiseaseModel);
        $container->id = $value->id;
        $container->version = $value->version;
        $container->status = $value->status;
    }
}
