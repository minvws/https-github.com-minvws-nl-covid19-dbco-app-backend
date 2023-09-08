<?php

declare(strict_types=1);

namespace App\Http\Responses\History;

use App\Models\Eloquent\OsirisHistory;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

class OsirisEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        /** @var OsirisHistory $value */
        $container->uuid = $value->uuid;
        $container->osirisValidationResponse = $value->osiris_validation_response;
        $container->caseIsReopened = $value->caseIsReopened();
        $container->status = $value->status;
        $container->time = $value->created_at;
    }
}
