<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Admin;

use App\Models\Policy\PolicyVersion;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

class PolicyVersionEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        /** @var PolicyVersion $value */
        $container->uuid = $value->uuid;
        $container->name = $value->name;
        $container->status = $value->status;
        $container->startDate = $value->start_date;
    }
}
