<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Admin;

use App\Models\Policy\DateOperation;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

final class DateOperationEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        /** @var DateOperation $value */
        $container->uuid = $value->uuid;
        $container->identifierType = $value->identifier_type;
        $container->originDateType = $value->origin_date_type;
        $container->relativeDay = $value->relativeDay;
    }
}
